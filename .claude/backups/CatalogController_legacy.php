<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Casts\ProductStatusCast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\ProductDetailResource;
use App\Http\Resources\Ecommerce\ProductResource;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Sale;
use App\Models\Ecommerce\SaleProduct;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\User;
use App\Services\MoneyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Public Catalog API Controller
 *
 * Provides product listing, filtering, search, and category browsing
 * for the storefront. Supports both guest and authenticated users.
 *
 * Stock Logic: FIFO (First In, First Out) - oldest stock cleared first
 * Price Logic: Shows original price + sale price if active sale exists
 */
final class CatalogController extends Controller
{
    private const CACHE_TTL = 300; // 5 minutes

    private const PER_PAGE = 12;

    /**
     * Get paginated product listing with filters
     */
    public function products(Request $request): JsonResponse
    {
        $query = Product::query()
            ->purchasable()
            ->whereNull('parent_id') // Only main products, not variants
            ->with([
                // Optimized: Only load displayImage for listing (faster)
                'media' => fn ($q) => $q->where('collection_name', 'displayImage'),
                'category',
                // FIFO: Load oldest available stock first (by priority then created_at)
                'availableStocks' => fn ($q) => $q->orderBy('priority')->orderBy('created_at')->limit(1),
            ])
            ->withStockInfo();

        // Apply filters
        $this->applySearchFilter($query, $request);
        $this->applyCategoryFilter($query, $request);
        $this->applyPriceFilter($query, $request);
        $this->applyStockFilter($query, $request);
        $this->applyFilterOptionsFilter($query, $request);
        $this->applySorting($query, $request);

        $products = $query->paginate($request->integer('per_page', self::PER_PAGE));

        // Load active sales for these products
        $productIds = $products->pluck('id')->toArray();
        $activeSales = $this->getActiveSalesForProducts($productIds);

        // Attach sale info to products for Resource
        $products->getCollection()->transform(function ($product) use ($activeSales) {
            $product->setRelation('activeSaleInfo', $activeSales[$product->id] ?? null);
            return $product;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => ProductResource::collection($products->getCollection()),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'has_more' => $products->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Get single product details
     */
    public function show(Product $product): JsonResponse
    {
        // Only show purchasable products
        if ($product->status !== ProductStatusCast::PUBLISHED) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $product->load([
            // Load both displayImage and bannerImage for detail page
            'media' => fn ($q) => $q->whereIn('collection_name', ['displayImage', 'bannerImage']),
            'category',
            'filterOptions.filter',
            'variants' => fn ($q) => $q->purchasable()->with([
                'media' => fn ($mq) => $mq->where('collection_name', 'displayImage'),
                // FIFO for variants too
                'availableStocks' => fn ($sq) => $sq->orderBy('priority')->orderBy('created_at'),
                'filterOptions.filter',
            ]),
            // FIFO: oldest stock first
            'availableStocks' => fn ($q) => $q->orderBy('priority')->orderBy('created_at'),
        ]);

        // Increment view count
        $product->increment('view_count');

        // Get active sale for this product
        $activeSales = $this->getActiveSalesForProducts([$product->id]);

        // Set sale info on resource
        $resource = (new ProductDetailResource($product))->setSaleInfo($activeSales[$product->id] ?? null);

        return response()->json([
            'success' => true,
            'data' => $resource->toArray(request()),
        ]);
    }

    /**
     * Get all active categories with product counts
     * Only returns categories that have products (directly or through descendants)
     */
    public function categories(): JsonResponse
    {
        $categories = Cache::remember('catalog_categories', self::CACHE_TTL, function () {
            return Category::query()
                ->where('status', true)
                ->whereNull('parent_id')
                ->with([
                    'children' => fn ($q) => $q->where('status', true)
                        ->withCount([
                            'products' => fn ($p) => $p->purchasable(),
                        ])
                        ->with([
                            // Load grandchildren for deeper nesting support
                            'children' => fn ($gc) => $gc->where('status', true)
                                ->withCount([
                                    'products' => fn ($p) => $p->purchasable(),
                                ]),
                        ]),
                ])
                ->withCount([
                    'products' => fn ($q) => $q->purchasable(),
                ])
                ->orderBy('order')
                ->get()
                ->map(function ($category) {
                    // Filter children to only those with products (direct or through grandchildren)
                    $filteredChildren = $category->children
                        ->map(function ($child) {
                            // Calculate total products including grandchildren
                            $grandchildrenCount = $child->children->sum('products_count');
                            $totalChildProducts = $child->products_count + $grandchildrenCount;

                            // Filter grandchildren to only those with products
                            $filteredGrandchildren = $child->children
                                ->filter(fn ($gc) => $gc->products_count > 0)
                                ->map(fn ($gc) => [
                                    'name' => $gc->name,
                                    'slug' => $gc->url,
                                    'product_count' => $gc->products_count,
                                ]);

                            return [
                                'name' => $child->name,
                                'slug' => $child->url,
                                'product_count' => $child->products_count,
                                'total_products' => $totalChildProducts,
                                'children' => $filteredGrandchildren->values()->toArray(),
                            ];
                        })
                        ->filter(fn ($child) => $child['total_products'] > 0);

                    // Calculate total products for parent (direct + all descendants)
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
                })
                // Only return parent categories that have products somewhere in hierarchy
                ->filter(fn ($category) => $category['total_products'] > 0)
                ->values();
        });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get single category with its products
     */
    public function category(Category $category, Request $request): JsonResponse
    {
        if (! $category->status) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Load children with product counts
        $category->load([
            'children' => fn ($q) => $q->where('status', true)
                ->withCount([
                    'products' => fn ($p) => $p->purchasable(),
                ])
                ->orderBy('order'),
        ]);

        // Get ancestors for breadcrumb
        $ancestors = $category->ancestors()
            ->where('status', true)
            ->get(['name', 'url'])
            ->map(fn ($ancestor) => [
                'name' => $ancestor->name,
                'slug' => $ancestor->url,
            ]);

        // Get all descendant category IDs for nested categories
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

        $this->applySearchFilter($query, $request);
        $this->applyPriceFilter($query, $request);
        $this->applyStockFilter($query, $request);
        $this->applySorting($query, $request);

        $products = $query->paginate($request->integer('per_page', self::PER_PAGE));

        // Load active sales
        $productIds = $products->pluck('id')->toArray();
        $activeSales = $this->getActiveSalesForProducts($productIds);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => [
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
                ],
                'items' => $products->map(fn ($product) => $this->formatProduct($product, $activeSales)),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'has_more' => $products->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Get featured/promoted products for homepage
     */
    public function featured(): JsonResponse
    {
        $data = Cache::remember('catalog_featured', self::CACHE_TTL, function () {
            // Best sellers (by view count for now, could be order count)
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

            // New arrivals
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

            // Get all product IDs for sales lookup
            $allProductIds = $bestSellers->pluck('id')
                ->merge($newArrivals->pluck('id'))
                ->unique()
                ->toArray();
            $activeSales = $this->getActiveSalesForProducts($allProductIds);

            // Attach sale info and use Resource
            $bestSellers->transform(function ($p) use ($activeSales) {
                $p->setRelation('activeSaleInfo', $activeSales[$p->id] ?? null);
                return $p;
            });
            $newArrivals->transform(function ($p) use ($activeSales) {
                $p->setRelation('activeSaleInfo', $activeSales[$p->id] ?? null);
                return $p;
            });

            return [
                'best_sellers' => ProductResource::collection($bestSellers),
                'new_arrivals' => ProductResource::collection($newArrivals),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get products currently on sale (Flash Deals / Sale Products)
     */
    public function onSale(Request $request): JsonResponse
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
        $this->applyCategoryFilter($query, $request);
        $this->applyPriceFilter($query, $request);

        $products = $query->paginate($request->integer('per_page', self::PER_PAGE));

        // Get active sales for all products
        $productIds = $products->pluck('id')->toArray();
        $activeSales = $this->getActiveSalesForProducts($productIds);

        // Filter to only products that have active sales
        $saleProducts = $products->getCollection()->filter(fn ($product) => isset($activeSales[$product->id]));

        // Attach sale info and use Resource
        $saleProducts->transform(function ($product) use ($activeSales) {
            $product->setRelation('activeSaleInfo', $activeSales[$product->id]);
            return $product;
        });

        // Get sale stats
        $totalDeals = SaleProduct::active()->count();
        $avgDiscount = SaleProduct::active()->avg('discount_percent') ?? 0;

        // Get earliest ending sale
        $earliestSale = Sale::active()->orderBy('ends_till')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_deals' => $totalDeals,
                    'avg_discount' => round($avgDiscount),
                    'ends_at' => $earliestSale?->ends_till?->toIso8601String(),
                ],
                'items' => ProductResource::collection($saleProducts->values()),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $saleProducts->count(),
                    'has_more' => $products->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Get top featured categories with products in stock
     * Only returns active categories that have products with available stock
     * Ordered by `order` column, limited to 6 for homepage display
     */
    public function featuredCategories(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 6);

        $categories = Cache::remember("catalog_featured_categories_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Category::query()
                ->where('status', true)
                ->whereNull('parent_id') // Only parent categories
                ->orderBy('order')
                ->get()
                ->filter(function ($category) {
                    // Get all category IDs (including descendants)
                    $categoryIds = $category->descendantsAndSelf()->pluck('id');

                    // Check if any product in these categories has stock
                    return Product::query()
                        ->purchasable()
                        ->whereIn('category_id', $categoryIds)
                        ->whereHas('availableStocks', fn ($q) => $q->where('in_stock_quantity', '>', 0))
                        ->exists();
                })
                ->take($limit)
                ->map(function ($category) {
                    // Get category IDs for product count
                    $categoryIds = $category->descendantsAndSelf()->pluck('id');

                    // Get products with stock count
                    $productCount = Product::query()
                        ->purchasable()
                        ->whereIn('category_id', $categoryIds)
                        ->whereHas('availableStocks', fn ($q) => $q->where('in_stock_quantity', '>', 0))
                        ->count();

                    // Get sample products for visual preview (4 product images)
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
                            'image' => $p->getFirstMediaUrl('displayImage'),
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
                })
                ->values();
        });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->input('q', '');

        if (strlen($search) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Search term must be at least 2 characters',
            ], 422);
        }

        $query = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with([
                'media' => fn ($q) => $q->where('collection_name', 'displayImage'),
                'category',
                'availableStocks' => fn ($q) => $q->orderBy('priority')->orderBy('created_at')->limit(1),
            ])
            ->withStockInfo()
            ->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('short_description', 'LIKE', "%{$search}%");
            });

        $this->applySorting($query, $request);

        $products = $query->paginate($request->integer('per_page', self::PER_PAGE));

        // Load active sales
        $productIds = $products->pluck('id')->toArray();
        $activeSales = $this->getActiveSalesForProducts($productIds);

        // Attach sale info to products for Resource
        $products->getCollection()->transform(function ($product) use ($activeSales) {
            $product->setRelation('activeSaleInfo', $activeSales[$product->id] ?? null);
            return $product;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'query' => $search,
                'items' => ProductResource::collection($products->getCollection()),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'has_more' => $products->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Get available filters for products (for filter sidebar)
     * Returns price range, sort options, and filter options (color, size, etc.)
     */
    public function filters(Request $request): JsonResponse
    {
        $cacheKey = 'catalog_filters_'.md5($request->input('category', 'all'));

        $filters = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($request) {
            $query = Product::query()->purchasable()->whereNull('parent_id');
            $categoryIds = null;

            if ($request->filled('category')) {
                $category = Category::where('url', $request->input('category'))->first();
                if ($category) {
                    $categoryIds = $category->descendantsAndSelf()->pluck('id');
                    $query->whereIn('category_id', $categoryIds);
                }
            }

            // Price range
            $priceRange = $query->clone()
                ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
                ->first();

            // Get filter options available for products in this category
            $filterOptions = $this->getAvailableFilterOptions($categoryIds);

            return [
                'price_range' => [
                    'min' => MoneyService::toRupees($priceRange->min_price ?? 0),
                    'max' => MoneyService::toRupees($priceRange->max_price ?? 0),
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

        return response()->json([
            'success' => true,
            'data' => $filters,
        ]);
    }

    /**
     * Get available filter options for products in given categories
     * Groups by filter name with product counts for each option
     */
    private function getAvailableFilterOptions($categoryIds): array
    {
        $query = \App\Models\Ecommerce\FilterOption::query()
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

        // Group by filter name
        $grouped = $options->groupBy(fn ($opt) => $opt->filter?->name ?? 'Other');

        return $grouped->map(function ($items, $filterName) {
            return [
                'name' => $filterName,
                'options' => $items->map(fn ($item) => [
                    'id' => $item->id,
                    'value' => $item->value,
                    'swatch' => $item->swatch_value,
                    'count' => $item->product_count,
                ])->values(),
            ];
        })->values()->toArray();
    }

    // ========================================
    // Private Filter Methods
    // ========================================

    private function applySearchFilter($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
    }

    private function applyCategoryFilter($query, Request $request): void
    {
        if ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $category = Category::where('url', $categorySlug)->first();

            if ($category) {
                $categoryIds = $category->descendantsAndSelf()->pluck('id');
                $query->whereIn('category_id', $categoryIds);
            }
        }
    }

    private function applyPriceFilter($query, Request $request): void
    {
        // Price is stored in paise, so multiply input by 100
        if ($request->filled('min_price')) {
            $minPaisa = (int) ($request->input('min_price') * 100);
            $query->where('price', '>=', $minPaisa);
        }

        if ($request->filled('max_price')) {
            $maxPaisa = (int) ($request->input('max_price') * 100);
            $query->where('price', '<=', $maxPaisa);
        }
    }

    private function applyStockFilter($query, Request $request): void
    {
        if ($request->boolean('in_stock')) {
            $query->inStock();
        }
    }

    private function applyFilterOptionsFilter($query, Request $request): void
    {
        // Filter by filter options (e.g., ?filters[Color]=1,2&filters[Size]=5)
        if ($request->filled('filters')) {
            $filterParams = $request->input('filters');

            if (is_array($filterParams)) {
                foreach ($filterParams as $filterOptionIds) {
                    // Parse comma-separated IDs
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
        }
    }

    private function applySorting($query, Request $request): void
    {
        $sort = $request->input('sort', 'popularity');

        match ($sort) {
            'latest' => $query->latest(),
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->orderByDesc('view_count'), // popularity
        };
    }

    // ========================================
    // Sales/Discount Methods
    // ========================================

    /**
     * Get current user's stage (subscription type) if logged in
     * Returns null for guests
     */
    private function getCurrentUserStage(): ?Stage
    {
        $user = auth('sanctum')->user();

        if (! $user instanceof User) {
            return null;
        }

        // Get active subscription
        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('status', UserSubscription::STATUS_ACTIVE)
            ->first();

        return $subscription?->stage;
    }

    /**
     * Get active sales for a list of product IDs
     * Returns array keyed by product_id with sale info
     *
     * Sales targeting logic:
     * - Guest users: Only see sales without specific targets (site-wide)
     * - Logged-in users: See site-wide sales + sales targeted to their stage
     */
    private function getActiveSalesForProducts(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $activeSales = [];
        $userStage = $this->getCurrentUserStage();

        // 1. Get product-specific sales (SaleProduct)
        $saleProductQuery = SaleProduct::query()
            ->active()
            ->whereIn('product_id', $productIds)
            ->ordered();

        // Filter by target: site-wide OR matching user's stage
        $saleProductQuery->where(function ($q) use ($userStage) {
            // Site-wide sales (no target)
            $q->whereNull('target_type')
                ->whereNull('target_id');

            // If user has a stage, also include sales targeting that stage
            if ($userStage) {
                $q->orWhere(function ($stageQuery) use ($userStage) {
                    $stageQuery->where('target_type', Stage::class)
                        ->where('target_id', $userStage->id);
                });
            }
        });

        $saleProducts = $saleProductQuery->get();

        foreach ($saleProducts as $sp) {
            if (! isset($activeSales[$sp->product_id]) && $sp->isActive()) {
                $activeSales[$sp->product_id] = [
                    'type' => 'product_sale',
                    'sale_product' => $sp,
                    'name' => $sp->sale?->name ?? 'Special Offer',
                    'ends_at' => $sp->ends_till ?? $sp->sale?->ends_till,
                ];
            }
        }

        // 2. Get site-wide or category-based sales
        $siteSales = Sale::query()
            ->active()
            ->ordered()
            ->get();

        foreach ($siteSales as $sale) {
            foreach ($productIds as $productId) {
                // Skip if already has a product-specific sale
                if (isset($activeSales[$productId])) {
                    continue;
                }

                // Check if sale applies to this product
                $product = Product::find($productId);
                if (! $product || ! $sale->appliesTo($product)) {
                    continue;
                }

                // Check sale targets
                $hasTargets = $sale->targets()->exists();

                if (! $hasTargets) {
                    // Site-wide sale - visible to everyone
                    $activeSales[$productId] = [
                        'type' => 'sale',
                        'sale' => $sale,
                        'name' => $sale->name,
                        'ends_at' => $sale->ends_till,
                    ];
                } elseif ($userStage) {
                    // Check if sale targets user's stage
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
                // Guest users don't see targeted sales
            }
        }

        return $activeSales;
    }

    /**
     * Calculate sale price for a product
     */
    private function calculateSalePrice(int $originalPrice, array $saleInfo): ?int
    {
        if (isset($saleInfo['sale_product'])) {
            /** @var SaleProduct $sp */
            $sp = $saleInfo['sale_product'];

            return $sp->getFinalPrice($originalPrice);
        }

        if (isset($saleInfo['sale'])) {
            /** @var Sale $sale */
            $sale = $saleInfo['sale'];

            return $sale->calculatePrice($originalPrice);
        }

        return null;
    }

    // ========================================
    // Formatting Methods
    // ========================================

    private function formatProduct(Product $product, array $activeSales = []): array
    {
        // FIFO: Get first available stock (oldest by priority + created_at)
        $stock = $product->availableStocks->first();
        $inStock = $product->available_stocks_count > 0 || $product->stocks_sum_in_stock_quantity > 0;

        // Get price from stock if available, otherwise from product
        $originalPrice = $stock?->getEffectivePrice() ?? $product->price;

        // Check for active sale
        $saleInfo = $activeSales[$product->id] ?? null;
        $salePrice = null;
        $discountPercent = null;
        $saleName = null;
        $saleEndsAt = null;

        if ($saleInfo) {
            $salePrice = $this->calculateSalePrice($originalPrice, $saleInfo);
            if ($salePrice && $salePrice < $originalPrice) {
                $discountPercent = round((($originalPrice - $salePrice) / $originalPrice) * 100);
                $saleName = $saleInfo['name'];
                $saleEndsAt = $saleInfo['ends_at']?->toIso8601String();
            } else {
                $salePrice = null; // No discount applied
            }
        }

        // Final display price
        $displayPrice = $salePrice ?? $originalPrice;

        return [
            'name' => $product->name,
            'slug' => $product->url,
            'sku' => $product->sku,
            // Pricing
            'price' => $displayPrice,
            'price_formatted' => MoneyService::format($displayPrice),
            'original_price' => $salePrice ? $originalPrice : null,
            'original_price_formatted' => $salePrice ? MoneyService::format($originalPrice) : null,
            'discount_percent' => $discountPercent,
            'sale_name' => $saleName,
            'sale_ends_at' => $saleEndsAt,
            // Category
            'category' => $product->category ? [
                'name' => $product->category->name,
                'slug' => $product->category->url,
            ] : null,
            // Responsive images with srcset using Spatie Media Library
            'image' => $this->getProductImage($product),
            'in_stock' => $inStock,
            'stock_quantity' => $product->total_stock,
            'view_count' => $product->view_count,
            // Affiliate points from FIFO stock
            'bv' => $stock?->bv ?? 0,
            'pv' => $stock?->pv ?? 0,
            'reward_points' => $stock?->reward_points ?? 0,
        ];
    }

    private function formatProductDetail(Product $product, array $activeSales = []): array
    {
        // FIFO: Get first available stock
        $stock = $product->availableStocks->first();
        $inStock = $product->availableStocks->count() > 0;
        $totalStock = $product->availableStocks->sum('in_stock_quantity');

        // Get price from stock
        $originalPrice = $stock?->getEffectivePrice() ?? $product->price;

        // Check for active sale
        $saleInfo = $activeSales[$product->id] ?? null;
        $salePrice = null;
        $discountPercent = null;
        $saleName = null;
        $saleEndsAt = null;

        if ($saleInfo) {
            $salePrice = $this->calculateSalePrice($originalPrice, $saleInfo);
            if ($salePrice && $salePrice < $originalPrice) {
                $discountPercent = round((($originalPrice - $salePrice) / $originalPrice) * 100);
                $saleName = $saleInfo['name'];
                $saleEndsAt = $saleInfo['ends_at']?->toIso8601String();
            } else {
                $salePrice = null;
            }
        }

        $displayPrice = $salePrice ?? $originalPrice;

        // Build gallery using Spatie Media Library with responsive images
        $gallery = $this->getProductGallery($product);

        // Format variants with their own FIFO stock and sales
        $variantIds = $product->variants->pluck('id')->toArray();
        $variantSales = $this->getActiveSalesForProducts($variantIds);

        $variants = $product->variants->map(function ($variant) use ($variantSales) {
            $variantStock = $variant->availableStocks->first();
            $variantOriginalPrice = $variantStock?->getEffectivePrice() ?? $variant->price;

            // Check sale for variant
            $variantSaleInfo = $variantSales[$variant->id] ?? null;
            $variantSalePrice = null;
            $variantDiscountPercent = null;

            if ($variantSaleInfo) {
                $variantSalePrice = $this->calculateSalePrice($variantOriginalPrice, $variantSaleInfo);
                if ($variantSalePrice && $variantSalePrice < $variantOriginalPrice) {
                    $variantDiscountPercent = round((($variantOriginalPrice - $variantSalePrice) / $variantOriginalPrice) * 100);
                } else {
                    $variantSalePrice = null;
                }
            }

            $variantDisplayPrice = $variantSalePrice ?? $variantOriginalPrice;

            return [
                'name' => $variant->name,
                'slug' => $variant->url,
                'sku' => $variant->sku,
                'price' => $variantDisplayPrice,
                'price_formatted' => MoneyService::format($variantDisplayPrice),
                'original_price' => $variantSalePrice ? $variantOriginalPrice : null,
                'original_price_formatted' => $variantSalePrice ? MoneyService::format($variantOriginalPrice) : null,
                'discount_percent' => $variantDiscountPercent,
                'image' => $this->getProductImage($variant),
                'in_stock' => $variant->availableStocks->count() > 0,
                'bv' => $variantStock?->bv ?? 0,
                'pv' => $variantStock?->pv ?? 0,
                'reward_points' => $variantStock?->reward_points ?? 0,
                'filter_options' => $variant->filterOptions->map(fn ($opt) => [
                    'filter' => $opt->filter?->name,
                    'value' => $opt->value,
                ]),
            ];
        });

        // Format filter options grouped by filter
        $filterOptions = $product->filterOptions->groupBy('filter_id')->map(function ($options) {
            $filter = $options->first()->filter;

            return [
                'filter_name' => $filter?->name,
                'options' => $options->map(fn ($opt) => [
                    'value' => $opt->value,
                ]),
            ];
        })->values();

        return [
            'name' => $product->name,
            'slug' => $product->url,
            'sku' => $product->sku,
            'description' => $product->description,
            'short_description' => $product->short_description,
            // Pricing
            'price' => $displayPrice,
            'price_formatted' => MoneyService::format($displayPrice),
            'original_price' => $salePrice ? $originalPrice : null,
            'original_price_formatted' => $salePrice ? MoneyService::format($originalPrice) : null,
            'discount_percent' => $discountPercent,
            'sale_name' => $saleName,
            'sale_ends_at' => $saleEndsAt,
            // Category
            'category' => $product->category ? [
                'name' => $product->category->name,
                'slug' => $product->category->url,
            ] : null,
            'gallery' => $gallery,
            'in_stock' => $inStock,
            'stock_quantity' => $totalStock,
            'view_count' => $product->view_count,
            // Return policy
            'is_returnable' => $product->is_returnable,
            'return_days' => $product->return_days,
            // Affiliate points from FIFO stock
            'bv' => $stock?->bv ?? 0,
            'pv' => $stock?->pv ?? 0,
            'reward_points' => $stock?->reward_points ?? 0,
            // Variants & options
            'has_variants' => $variants->isNotEmpty(),
            'variants' => $variants,
            'filter_options' => $filterOptions,
        ];
    }

    /**
     * Get product image with responsive srcsets using Spatie Media Library
     * Returns optimized responsive images for fast loading across devices
     */
    private function getProductImage(Product $product): ?array
    {
        $displayMedia = $product->getFirstMedia('displayImage');

        if (! $displayMedia) {
            return null;
        }

        // Check if responsive images exist
        $hasResponsive = $displayMedia->hasResponsiveImages();

        return [
            'url' => url($displayMedia->getUrl()),
            'thumbnail' => url($displayMedia->hasGeneratedConversion('thumb')
                ? $displayMedia->getUrl('thumb')
                : $displayMedia->getUrl()),
            'srcset' => $hasResponsive ? $displayMedia->getSrcset() : null,
            'responsive' => $hasResponsive ? $displayMedia->getResponsiveImageUrls() : null,
            'alt' => $displayMedia->name,
            'width' => $displayMedia->width ?? null,
            'height' => $displayMedia->height ?? null,
        ];
    }

    /**
     * Get product gallery images with responsive srcsets
     */
    private function getProductGallery(Product $product): array
    {
        $gallery = [];

        // Add display image first
        $displayMedia = $product->getFirstMedia('displayImage');
        if ($displayMedia) {
            $hasResponsive = $displayMedia->hasResponsiveImages();
            $gallery[] = [
                'id' => $displayMedia->id,
                'url' => url($displayMedia->getUrl()),
                'thumbnail' => url($displayMedia->hasGeneratedConversion('thumb')
                    ? $displayMedia->getUrl('thumb')
                    : $displayMedia->getUrl()),
                'srcset' => $hasResponsive ? $displayMedia->getSrcset() : null,
                'responsive' => $hasResponsive ? $displayMedia->getResponsiveImageUrls() : null,
            ];
        }

        // Add banner images
        foreach ($product->getMedia('bannerImage') as $media) {
            $hasResponsive = $media->hasResponsiveImages();
            $gallery[] = [
                'id' => $media->id,
                'url' => url($media->getUrl()),
                'thumbnail' => url($media->hasGeneratedConversion('thumb')
                    ? $media->getUrl('thumb')
                    : $media->getUrl()),
                'srcset' => $hasResponsive ? $media->getSrcset() : null,
                'responsive' => $hasResponsive ? $media->getResponsiveImageUrls() : null,
            ];
        }

        return $gallery;
    }
}
