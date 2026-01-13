<?php

declare(strict_types=1);

namespace App\Services\Ecommerce;

use App\Casts\ProductStatusCast;
use App\Http\Resources\Ecommerce\ProductDetailResource;
use App\Http\Resources\Ecommerce\ProductResource;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\FilterOption;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Catalog Service - Handles all catalog business logic
 *
 * Product listing, filtering, search, category browsing, sales calculation
 */
final class CatalogService
{
    private const CACHE_TTL = 300; // 5 minutes

    private const PER_PAGE = 12;

    /**
     * Get paginated products with filters
     */
    public function getProducts(array $filters = [], int $page = 1, int $perPage = self::PER_PAGE): array
    {
        $query = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with([
                'media' => fn ($q) => $q->where('collection_name', 'displayImage'),
                'category',
                'availableStocks' => fn ($q) => $q->orderBy('priority')->orderBy('created_at')->limit(1),
            ])
            ->withStockInfo();

        // Apply filters
        $this->applySearchFilter($query, $filters['search'] ?? null);
        $this->applyCategoryFilter($query, $filters['category'] ?? null);
        $this->applyPriceFilter($query, $filters);
        $this->applyStockFilter($query, $filters['in_stock'] ?? false);
        $this->applyFilterOptionsFilter($query, $filters['filters'] ?? []);
        $this->applySorting($query, $filters['sort'] ?? 'popularity');

        $products = $query->paginate($perPage, ['*'], 'page', $page);
        $productIds = $products->pluck('id')->toArray();
        $activeSales = $this->getActiveSalesForProducts($productIds);

        return [
            'items' => ProductResource::collection($products)->toArray(request()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'has_more' => $products->hasMorePages(),
            ],
        ];
    }

    /**
     * Get product details
     */
    public function getProductDetail(string $slug): ?array
    {
        $product = Product::where('url', $slug)
            ->where('status', ProductStatusCast::PUBLISHED)
            ->with([
                'media' => fn ($q) => $q->whereIn('collection_name', ['displayImage', 'bannerImage']),
                'category',
                'filterGroup.filters.options',
                'filterOptions.filter',
                'variants' => fn ($q) => $q->purchasable()->with([
                    'media' => fn ($mq) => $mq->where('collection_name', 'displayImage'),
                    'availableStocks' => fn ($sq) => $sq->orderBy('priority')->orderBy('created_at'),
                ]),
                'availableStocks' => fn ($q) => $q->orderBy('priority')->orderBy('created_at'),
            ])
            ->first();

        if (! $product) {
            return null;
        }

        $product->increment('view_count');
        $activeSales = $this->getActiveSalesForProducts([$product->id]);

        return (new ProductDetailResource($product))->toArray(request());
    }

    /**
     * Get all active categories with product counts
     */
    public function getCategories(): array
    {
        return Cache::remember('catalog_categories', self::CACHE_TTL, function () {
            return Category::query()
                ->where('status', true)
                ->whereNull('parent_id')
                ->with([
                    'children' => fn ($q) => $q->where('status', true)
                        ->withCount(['products' => fn ($p) => $p->purchasable()])
                        ->with([
                            'children' => fn ($gc) => $gc->where('status', true)
                                ->withCount(['products' => fn ($p) => $p->purchasable()]),
                        ]),
                ])
                ->withCount(['products' => fn ($q) => $q->purchasable()])
                ->orderBy('order')
                ->get()
                ->map(fn ($category) => $this->formatCategoryListing($category))
                ->filter(fn ($category) => $category['total_products'] > 0)
                ->values();
        });
    }

    /**
     * Get category with products
     */
    public function getCategory(string $slug, array $filters = [], int $page = 1, int $perPage = self::PER_PAGE): ?array
    {
        $category = Category::where('url', $slug)
            ->where('status', true)
            ->with([
                'children' => fn ($q) => $q->where('status', true)
                    ->withCount(['products' => fn ($p) => $p->purchasable()])
                    ->orderBy('order'),
            ])
            ->first();

        if (! $category) {
            return null;
        }

        $ancestors = $category->ancestors()
            ->where('status', true)
            ->get(['name', 'url'])
            ->map(fn ($ancestor) => [
                'name' => $ancestor->name,
                'slug' => $ancestor->url,
            ]);

        $categoryIds = $category->descendantsAndSelf()->pluck('id');

        $query = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->whereIn('category_id', $categoryIds)
            ->with([
                'media' => fn ($q) => $q->where('collection_name', 'displayImage'),
                'category',
                'availableStocks' => fn ($q) => $q->orderBy('priority')->orderBy('created_at')->limit(1),
            ])
            ->withStockInfo();

        $this->applySearchFilter($query, $filters['search'] ?? null);
        $this->applyPriceFilter($query, $filters);
        $this->applyStockFilter($query, $filters['in_stock'] ?? false);
        $this->applySorting($query, $filters['sort'] ?? 'popularity');

        $products = $query->paginate($perPage, ['*'], 'page', $page);
        $activeSales = $this->getActiveSalesForProducts($products->pluck('id')->toArray());

        return [
            'category' => $this->formatCategoryDetail($category, $ancestors),
            'items' => ProductResource::collection($products)->toArray(request()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'has_more' => $products->hasMorePages(),
            ],
        ];
    }

    /**
     * Get featured products (best sellers + new arrivals)
     */
    public function getFeatured(): array
    {
        return Cache::remember('catalog_featured', self::CACHE_TTL, function () {
            $bestSellers = Product::query()
                ->purchasable()
                ->whereNull('parent_id')
                ->with([
                    'media' => fn ($q) => $q->where('collection_name', 'displayImage'),
                    'category',
                    'availableStocks' => fn ($q) => $q->orderBy('priority')->orderBy('created_at')->limit(1),
                ])
                ->withStockInfo()
                ->orderByDesc('view_count')
                ->limit(8)
                ->get();

            $newArrivals = Product::query()
                ->purchasable()
                ->whereNull('parent_id')
                ->with([
                    'media' => fn ($q) => $q->where('collection_name', 'displayImage'),
                    'category',
                    'availableStocks' => fn ($q) => $q->orderBy('priority')->orderBy('created_at')->limit(1),
                ])
                ->withStockInfo()
                ->latest()
                ->limit(8)
                ->get();

            $allProductIds = $bestSellers->pluck('id')
                ->merge($newArrivals->pluck('id'))
                ->unique()
                ->toArray();
            $activeSales = $this->getActiveSalesForProducts($allProductIds);

            return [
                'best_sellers' => ProductResource::collection($bestSellers)->toArray(request()),
                'new_arrivals' => ProductResource::collection($newArrivals)->toArray(request()),
            ];
        });
    }

    /**
     * Get products on sale
     */
    public function getOnSale(array $filters = [], int $page = 1, int $perPage = self::PER_PAGE): array
    {
        $query = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with([
                'media' => fn ($q) => $q->where('collection_name', 'displayImage'),
                'category',
                'availableStocks' => fn ($q) => $q->orderBy('priority')->orderBy('created_at')->limit(1),
            ])
            ->withStockInfo();

        $this->applyCategoryFilter($query, $filters['category'] ?? null);
        $this->applyPriceFilter($query, $filters);
        $this->applyStockFilter($query, $filters['in_stock'] ?? false);
        $this->applySorting($query, $filters['sort'] ?? 'popularity');

        $products = $query->paginate($perPage, ['*'], 'page', $page);
        $productIds = $products->pluck('id')->toArray();
        $activeSales = $this->getActiveSalesForProducts($productIds);

        $saleProducts = $products->filter(fn ($product) => isset($activeSales[$product->id]));

        $totalDeals = SaleProduct::active()->count();
        $avgDiscount = SaleProduct::active()->avg('discount_percent') ?? 0;
        $earliestSale = Sale::active()->orderBy('ends_till')->first();

        $items = $saleProducts->map(function ($product) use ($activeSales) {
            $formatted = ProductResource::make($product)->toArray(request());
            $saleInfo = $activeSales[$product->id] ?? null;

            return array_merge($formatted, [
                'sale_ends_at' => $saleInfo['ends_at']?->toIso8601String() ?? null,
            ]);
        })->values();

        return [
            'stats' => [
                'total_deals' => $totalDeals,
                'avg_discount' => round($avgDiscount),
                'ends_at' => $earliestSale?->ends_till?->toIso8601String(),
            ],
            'items' => $items,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $saleProducts->count(),
                'has_more' => $products->hasMorePages(),
            ],
        ];
    }

    /**
     * Get featured categories
     */
    public function getFeaturedCategories(int $limit = 6): array
    {
        return Cache::remember("catalog_featured_categories_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Category::query()
                ->where('status', true)
                ->whereNull('parent_id')
                ->orderBy('order')
                ->get()
                ->filter(fn ($category) => $this->categoryHasStock($category))
                ->take($limit)
                ->map(fn ($category) => $this->formatFeaturedCategory($category))
                ->values();
        });
    }

    /**
     * Search products
     */
    public function search(string $query, array $filters = [], int $page = 1, int $perPage = self::PER_PAGE): array
    {
        if (strlen($query) < 2) {
            throw new \InvalidArgumentException('Search term must be at least 2 characters');
        }

        $productQuery = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with([
                'media' => fn ($q) => $q->where('collection_name', 'displayImage'),
                'category',
                'availableStocks' => fn ($q) => $q->orderBy('priority')->orderBy('created_at')->limit(1),
            ])
            ->withStockInfo()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('sku', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhere('short_description', 'LIKE', "%{$query}%");
            });

        $this->applySorting($productQuery, $filters['sort'] ?? 'popularity');

        $products = $productQuery->paginate($perPage, ['*'], 'page', $page);
        $activeSales = $this->getActiveSalesForProducts($products->pluck('id')->toArray());

        return [
            'query' => $query,
            'items' => ProductResource::collection($products)->toArray(request()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'has_more' => $products->hasMorePages(),
            ],
        ];
    }

    /**
     * Get available filters
     */
    public function getFilters(?string $categorySlug = null): array
    {
        $cacheKey = 'catalog_filters_'.md5($categorySlug ?? 'all');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($categorySlug) {
            $query = Product::query()->purchasable()->whereNull('parent_id');
            $categoryIds = null;

            if ($categorySlug) {
                $category = Category::where('url', $categorySlug)->first();
                if ($category) {
                    $categoryIds = $category->descendantsAndSelf()->pluck('id');
                    $query->whereIn('category_id', $categoryIds);
                }
            }

            $priceRange = $query->clone()
                ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
                ->first();

            $filterOptions = $this->getAvailableFilterOptions($categoryIds);

            return [
                'price_range' => [
                    'min' => \App\Services\MoneyService::toRupees($priceRange->min_price ?? 0),
                    'max' => \App\Services\MoneyService::toRupees($priceRange->max_price ?? 0),
                ],
                'sort_options' => [
                    ['value' => 'popularity', 'label' => 'Popularity'],
                    ['value' => 'latest', 'label' => 'Newest First'],
                    ['value' => 'price_asc', 'label' => 'Price: Low to High'],
                    ['value' => 'price_desc', 'label' => 'Price: High to Low'],
                    ['value' => 'name_asc', 'label' => 'Name: A to Z'],
                    ['value' => 'name_desc', 'label' => 'Name: Z to A'],
                ],
                'filter_options' => $filterOptions,
            ];
        });
    }

    // ========================================
    // Private Helper Methods
    // ========================================

    private function applySearchFilter($query, ?string $search): void
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
    }

    private function applyCategoryFilter($query, ?string $categorySlug): void
    {
        if ($categorySlug) {
            $category = Category::where('url', $categorySlug)->first();

            if ($category) {
                $categoryIds = $category->descendantsAndSelf()->pluck('id');
                $query->whereIn('category_id', $categoryIds);
            }
        }
    }

    private function applyPriceFilter($query, array $filters): void
    {
        if (isset($filters['min_price'])) {
            $minPaisa = (int) ($filters['min_price'] * 100);
            $query->where('price', '>=', $minPaisa);
        }

        if (isset($filters['max_price'])) {
            $maxPaisa = (int) ($filters['max_price'] * 100);
            $query->where('price', '<=', $maxPaisa);
        }
    }

    private function applyStockFilter($query, bool $inStock): void
    {
        if ($inStock) {
            $query->inStock();
        }
    }

    private function applyFilterOptionsFilter($query, array $filters): void
    {
        foreach ($filters as $filterOptionIds) {
            $optionIds = is_string($filterOptionIds)
                ? array_map('intval', explode(',', $filterOptionIds))
                : (array) $filterOptionIds;

            if (! empty($optionIds)) {
                $query->whereHas('filterOptions', function ($q) use ($optionIds) {
                    $q->whereIn('filter_options.id', $optionIds);
                });
            }
        }
    }

    private function applySorting($query, string $sort): void
    {
        match ($sort) {
            'latest' => $query->latest(),
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->orderByDesc('view_count'),
        };
    }

    private function categoryHasStock(Category $category): bool
    {
        $categoryIds = $category->descendantsAndSelf()->pluck('id');

        return Product::query()
            ->purchasable()
            ->whereIn('category_id', $categoryIds)
            ->whereHas('availableStocks', fn ($q) => $q->where('in_stock_quantity', '>', 0))
            ->exists();
    }

    private function formatCategoryListing(Category $category): array
    {
        $filteredChildren = $category->children
            ->map(fn ($child) => $this->formatCategoryChild($child))
            ->filter(fn ($child) => $child['total_products'] > 0);

        $descendantCount = $filteredChildren->sum('total_products');
        $totalProducts = $category->products_count + $descendantCount;

        return [
            'name' => $category->name,
            'slug' => $category->url,
            'product_count' => $category->products_count,
            'total_products' => $totalProducts,
            'thumbnail' => $category->getFirstMediaUrl('thumbnail') ? url($category->getFirstMediaUrl('thumbnail')) : null,
            'children' => $filteredChildren->values()->toArray(),
        ];
    }

    private function formatCategoryChild(Category $child): array
    {
        $grandchildrenCount = $child->children->sum('products_count');
        $totalChildProducts = $child->products_count + $grandchildrenCount;

        $filteredGrandchildren = $child->children
            ->filter(fn ($gc) => $gc->products_count > 0)
            ->map(fn ($gc) => [
                'name' => $gc->name,
                'slug' => $gc->url,
                'product_count' => $gc->products_count,
                'thumbnail' => $gc->getFirstMediaUrl('thumbnail') ? url($gc->getFirstMediaUrl('thumbnail')) : null,
            ]);

        return [
            'name' => $child->name,
            'slug' => $child->url,
            'product_count' => $child->products_count,
            'total_products' => $totalChildProducts,
            'children' => $filteredGrandchildren->values()->toArray(),
        ];
    }

    private function formatCategoryDetail(Category $category, $ancestors): array
    {
        return [
            'name' => $category->name,
            'slug' => $category->url,
            'description' => $category->desc,
            'thumbnail' => $category->getFirstMediaUrl('thumbnail') ? url($category->getFirstMediaUrl('thumbnail')) : null,
            'banner' => $category->getFirstMediaUrl('banner') ? url($category->getFirstMediaUrl('banner')) : null,
            'seo_meta' => $category->seo_meta,
            'children' => $category->children->map(fn ($child) => [
                'name' => $child->name,
                'slug' => $child->url,
                'thumbnail' => $child->getFirstMediaUrl('thumbnail') ? url($child->getFirstMediaUrl('thumbnail')) : null,
                'product_count' => $child->products_count,
            ]),
            'ancestors' => $ancestors,
        ];
    }

    private function formatFeaturedCategory(Category $category): array
    {
        $categoryIds = $category->descendantsAndSelf()->pluck('id');

        $productCount = Product::query()
            ->purchasable()
            ->whereIn('category_id', $categoryIds)
            ->whereHas('availableStocks', fn ($q) => $q->where('in_stock_quantity', '>', 0))
            ->count();

        $sampleProducts = Product::query()
            ->purchasable()
            ->whereIn('category_id', $categoryIds)
            ->whereHas('availableStocks', fn ($q) => $q->where('in_stock_quantity', '>', 0))
            ->with(['media' => fn ($q) => $q->where('collection_name', 'displayImage')])
            ->orderByDesc('view_count')
            ->limit(4)
            ->get()
            ->map(fn ($p) => [
                'name' => $p->name,
                'image' => $p->getFirstMediaUrl('displayImage') ? url($p->getFirstMediaUrl('displayImage')) : null,
            ]);

        return [
            'name' => $category->name,
            'slug' => $category->url,
            'description' => $category->desc,
            'thumbnail' => $category->getFirstMediaUrl('thumbnail') ? url($category->getFirstMediaUrl('thumbnail')) : null,
            'banner' => $category->getFirstMediaUrl('banner') ? url($category->getFirstMediaUrl('banner')) : null,
            'product_count' => $productCount,
            'sample_products' => $sampleProducts,
        ];
    }

    private function getAvailableFilterOptions($categoryIds): array
    {
        $query = FilterOption::query()
            ->select('filter_options.id', 'filter_options.filter_id', 'filter_options.value', 'filter_options.swatch_value')
            ->join('product_filter_options', 'filter_options.id', '=', 'product_filter_options.filter_option_id')
            ->join('products', 'products.id', '=', 'product_filter_options.product_id')
            ->where('products.status', ProductStatusCast::PUBLISHED)
            ->whereNull('products.parent_id');

        if ($categoryIds) {
            $query->whereIn('products.category_id', $categoryIds);
        }

        $options = $query
            ->selectRaw('COUNT(DISTINCT products.id) as product_count')
            ->groupBy('filter_options.id', 'filter_options.filter_id', 'filter_options.value', 'filter_options.swatch_value')
            ->with('filter:id,name')
            ->get();

        return $options->groupBy(fn ($opt) => $opt->filter?->name ?? 'Other')
            ->map(fn ($items, $filterName) => [
                'name' => $filterName,
                'options' => $items->map(fn ($item) => [
                    'id' => $item->id,
                    'value' => $item->value,
                    'swatch' => $item->swatch_value,
                    'count' => $item->product_count,
                ])->values(),
            ])->values()->toArray();
    }

    private function getCurrentUserStage(): ?Stage
    {
        $user = auth('sanctum')->user();

        if (! $user instanceof User) {
            return null;
        }

        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('status', UserSubscription::STATUS_ACTIVE)
            ->first();

        return $subscription?->stage;
    }

    private function getActiveSalesForProducts(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $activeSales = [];
        $userStage = $this->getCurrentUserStage();

        // Product-specific sales
        $saleProductQuery = SaleProduct::query()
            ->active()
            ->whereIn('product_id', $productIds)
            ->ordered()
            ->where(function ($q) use ($userStage) {
                $q->whereNull('target_type')->whereNull('target_id');

                if ($userStage) {
                    $q->orWhere(fn ($stageQuery) => $stageQuery
                        ->where('target_type', Stage::class)
                        ->where('target_id', $userStage->id)
                    );
                }
            });

        foreach ($saleProductQuery->get() as $sp) {
            if (! isset($activeSales[$sp->product_id]) && $sp->isActive()) {
                $activeSales[$sp->product_id] = [
                    'type' => 'product_sale',
                    'sale_product' => $sp,
                    'name' => $sp->sale?->name ?? 'Special Offer',
                    'ends_at' => $sp->ends_till ?? $sp->sale?->ends_till,
                ];
            }
        }

        // Site-wide sales
        $siteSales = Sale::query()->active()->ordered()->get();

        foreach ($siteSales as $sale) {
            foreach ($productIds as $productId) {
                if (isset($activeSales[$productId])) {
                    continue;
                }

                $product = Product::find($productId);
                if (! $product || ! $sale->appliesTo($product)) {
                    continue;
                }

                $hasTargets = $sale->targets()->exists();

                if (! $hasTargets) {
                    $activeSales[$productId] = [
                        'type' => 'sale',
                        'sale' => $sale,
                        'name' => $sale->name,
                        'ends_at' => $sale->ends_till,
                    ];
                } elseif ($userStage) {
                    $stageTargeted = $sale->targets()
                        ->where('target_type', Stage::class)
                        ->where('target_id', $userStage->id)
                        ->exists();

                    if ($stageTargeted) {
                        $activeSales[$productId] = [
                            'type' => 'sale',
                            'sale' => $sale,
                            'name' => $sale->name,
                            'ends_at' => $sale->ends_till,
                        ];
                    }
                }
            }
        }

        return $activeSales;
    }
}
