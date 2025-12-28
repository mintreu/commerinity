<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Casts\ProductStatusCast;
use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Services\MoneyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Public Catalog API Controller
 *
 * Provides product listing, filtering, search, and category browsing
 * for the storefront. Supports both guest and authenticated users.
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
                'productDisplay',
                'category',
                'availableStocks' => fn ($q) => $q->orderBy('priority')->limit(1),
            ])
            ->withStockInfo();

        // Apply filters
        $this->applySearchFilter($query, $request);
        $this->applyCategoryFilter($query, $request);
        $this->applyPriceFilter($query, $request);
        $this->applyStockFilter($query, $request);
        $this->applySorting($query, $request);

        $products = $query->paginate($request->integer('per_page', self::PER_PAGE));

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $products->map(fn ($product) => $this->formatProduct($product)),
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
            'productDisplay',
            'productGallery',
            'category',
            'filterGroup.filters.options',
            'filterOptions.filter',
            'variants' => fn ($q) => $q->purchasable()->with(['productDisplay', 'availableStocks']),
            'availableStocks' => fn ($q) => $q->orderBy('priority'),
        ]);

        // Increment view count
        $product->increment('view_count');

        return response()->json([
            'success' => true,
            'data' => $this->formatProductDetail($product),
        ]);
    }

    /**
     * Get all active categories with product counts
     */
    public function categories(): JsonResponse
    {
        $categories = Cache::remember('catalog_categories', self::CACHE_TTL, function () {
            return Category::query()
                ->where('status', true)
                ->whereNull('parent_id')
                ->with([
                    'children' => fn ($q) => $q->where('status', true)->withCount([
                        'products' => fn ($p) => $p->purchasable(),
                    ]),
                ])
                ->withCount([
                    'products' => fn ($q) => $q->purchasable(),
                ])
                ->orderBy('order')
                ->get()
                ->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->url,
                    'product_count' => $category->products_count,
                    'thumbnail' => $category->getFirstMediaUrl('thumbnail'),
                    'children' => $category->children->map(fn ($child) => [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->url,
                        'product_count' => $child->products_count,
                    ]),
                ]);
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

        // Get all descendant category IDs for nested categories
        $categoryIds = $category->descendantsAndSelf()->pluck('id');

        $query = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->whereIn('category_id', $categoryIds)
            ->with(['productDisplay', 'category', 'availableStocks'])
            ->withStockInfo();

        $this->applySearchFilter($query, $request);
        $this->applyPriceFilter($query, $request);
        $this->applyStockFilter($query, $request);
        $this->applySorting($query, $request);

        $products = $query->paginate($request->integer('per_page', self::PER_PAGE));

        return response()->json([
            'success' => true,
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->url,
                    'description' => $category->desc,
                    'thumbnail' => $category->getFirstMediaUrl('thumbnail'),
                    'banner' => $category->getFirstMediaUrl('banner'),
                ],
                'items' => $products->map(fn ($product) => $this->formatProduct($product)),
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
                ->with(['productDisplay', 'category', 'availableStocks'])
                ->withStockInfo()
                ->orderByDesc('view_count')
                ->limit(8)
                ->get()
                ->map(fn ($p) => $this->formatProduct($p));

            // New arrivals
            $newArrivals = Product::query()
                ->purchasable()
                ->whereNull('parent_id')
                ->with(['productDisplay', 'category', 'availableStocks'])
                ->withStockInfo()
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn ($p) => $this->formatProduct($p));

            return [
                'best_sellers' => $bestSellers,
                'new_arrivals' => $newArrivals,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
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
            ->with(['productDisplay', 'category', 'availableStocks'])
            ->withStockInfo()
            ->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('short_description', 'LIKE', "%{$search}%");
            });

        $this->applySorting($query, $request);

        $products = $query->paginate($request->integer('per_page', self::PER_PAGE));

        return response()->json([
            'success' => true,
            'data' => [
                'query' => $search,
                'items' => $products->map(fn ($product) => $this->formatProduct($product)),
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
     */
    public function filters(Request $request): JsonResponse
    {
        $cacheKey = 'catalog_filters_'.md5($request->input('category', 'all'));

        $filters = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($request) {
            $query = Product::query()->purchasable()->whereNull('parent_id');

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
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $filters,
        ]);
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
    // Formatting Methods
    // ========================================

    private function formatProduct(Product $product): array
    {
        $stock = $product->availableStocks->first();
        $inStock = $product->available_stocks_count > 0 || $product->stocks_sum_in_stock_quantity > 0;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->url,
            'sku' => $product->sku,
            'price' => $product->price,
            'price_formatted' => MoneyService::format($product->price),
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->url,
            ] : null,
            'image' => $product->productDisplay?->url ?? null,
            'in_stock' => $inStock,
            'stock_quantity' => $product->total_stock,
            'view_count' => $product->view_count,
            // MLM points from first available stock
            'bv' => $stock?->bv ?? 0,
            'pv' => $stock?->pv ?? 0,
            'reward_points' => $stock?->reward_points ?? 0,
        ];
    }

    private function formatProductDetail(Product $product): array
    {
        $stock = $product->availableStocks->first();
        $inStock = $product->availableStocks->count() > 0;
        $totalStock = $product->availableStocks->sum('in_stock_quantity');

        // Build gallery from productGallery relation
        $gallery = $product->productGallery->map(fn ($media) => [
            'id' => $media->id,
            'url' => $media->url,
            'thumbnail' => $media->url, // Could use thumbnail conversion if available
        ])->toArray();

        // Add main display image as first gallery item
        if ($product->productDisplay) {
            array_unshift($gallery, [
                'id' => $product->productDisplay->id,
                'url' => $product->productDisplay->url,
                'thumbnail' => $product->productDisplay->url,
            ]);
        }

        // Format variants if any
        $variants = $product->variants->map(fn ($variant) => [
            'id' => $variant->id,
            'name' => $variant->name,
            'sku' => $variant->sku,
            'price' => $variant->price,
            'price_formatted' => MoneyService::format($variant->price),
            'image' => $variant->productDisplay?->url,
            'in_stock' => $variant->availableStocks->count() > 0,
            'filter_options' => $variant->filterOptions->map(fn ($opt) => [
                'filter' => $opt->filter?->name,
                'value' => $opt->value,
            ]),
        ]);

        // Format filter options grouped by filter
        $filterOptions = $product->filterOptions->groupBy('filter_id')->map(function ($options) {
            $filter = $options->first()->filter;

            return [
                'filter_name' => $filter?->name,
                'options' => $options->map(fn ($opt) => [
                    'id' => $opt->id,
                    'value' => $opt->value,
                ]),
            ];
        })->values();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->url,
            'sku' => $product->sku,
            'description' => $product->description,
            'short_description' => $product->short_description,
            'price' => $product->price,
            'price_formatted' => MoneyService::format($product->price),
            'category' => $product->category ? [
                'id' => $product->category->id,
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
            // MLM points
            'bv' => $stock?->bv ?? 0,
            'pv' => $stock?->pv ?? 0,
            'reward_points' => $stock?->reward_points ?? 0,
            // Variants & options
            'has_variants' => $variants->isNotEmpty(),
            'variants' => $variants,
            'filter_options' => $filterOptions,
        ];
    }
}
