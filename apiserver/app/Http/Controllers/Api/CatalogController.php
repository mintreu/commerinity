<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\CategoryResource;
use App\Http\Resources\Ecommerce\FilterResource;
use App\Http\Resources\Ecommerce\ProductResource;
use App\Http\Resources\Ecommerce\ProductDetailResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Filter;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Get all products with filtering
     */
//    public function products(Request $request): AnonymousResourceCollection
//    {
//        $products = Product::query()
//            ->purchasable()
//            ->whereNull('parent_id')
//            ->with(['category', 'media'])
//            // Load stock for price calculation and availability
//            ->withStockInfo()
//            // Apply Scopes
//            ->search($request->input('search'))
//            ->byCategory($request->input('category'))
//            // Filter options logic (TBD if needed via scope, currently basic)
//            // Price range filter
//            ->byPrice(
//                $request->input('min_price') ? (int) $request->input('min_price') : null,
//                $request->input('max_price') ? (int) $request->input('max_price') : null
//            )
//            ->sort($request->input('sort'))
//            ->paginate($request->input('per_page', 20));
//
//        // Pass context if needed via additional data wrapper or transform
//        return ProductResource::collection($products);
//    }



    public function products(Request $request): AnonymousResourceCollection
    {
        $query = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with(['category', 'media'])
            ->withStockInfo()
            ->search($request->input('search'))
            ->byCategory($request->input('category'))
            ->byPrice(
                $request->input('min_price') ? (int) $request->input('min_price') : null,
                $request->input('max_price') ? (int) $request->input('max_price') : null
            );

        /* ===============================
           ✅ FIXED FILTER PARSING
           =============================== */
        if ($request->filled('filters')) {

            // Frontend sends JSON string
            $filters = json_decode($request->input('filters'), true);

            if (is_array($filters)) {
                foreach ($filters as $filterName => $optionIds) {

                    // "1,2,3" → [1,2,3]
                    if (is_string($optionIds)) {
                        $optionIds = array_filter(
                            array_map('intval', explode(',', $optionIds))
                        );
                    }

                    if (empty($optionIds)) {
                        continue;
                    }

                    $query->where(function ($q) use ($optionIds) {
                        $q->whereHas('filterOptions', function ($qq) use ($optionIds) {
                            $qq->whereIn('filter_options.id', $optionIds);
                        })
                            ->orWhereHas('variants.filterOptions', function ($qq) use ($optionIds) {
                                $qq->whereIn('filter_options.id', $optionIds);
                            });
                    });
                }
            }
        }

        $products = $query
            ->sort($request->input('sort'))
            ->paginate($request->input('per_page', 20));

        return ProductResource::collection($products);
    }







    /**
     * Get single product detail
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        if ($product->status->value !== 'Published') {
            return response()->json(['message' => 'Product not available'], 404);
        }

        $product->load([
            'category',
            'media',
            'variants' => function ($q) {
                $q->with('media', 'availableStocks')->purchasable();
            },
            'availableStocks',
            'filterOptions.filter',
        ]);

        $resourceData = (new ProductDetailResource($product))->toArray($request);

        return response()->json([
            'success' => true,
            'data' => $resourceData,
        ]);
    }

    /**
     * Get products by category
     */
    public function category(Request $request, Category $category): JsonResponse
    {
        $category->loadMissing('media');

        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        $productsQuery = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with(['category', 'media'])
            ->withStockInfo()
            ->search($request->input('search'))
            ->where('category_id', $category->id)
            ->byPrice(
                $request->input('min_price') ? (int) $request->input('min_price') : null,
                $request->input('max_price') ? (int) $request->input('max_price') : null
            )
            ->sort($request->input('sort'))
            ->paginate($perPage, ['*'], 'page', $page);

        $resourceCollection = ProductResource::collection($productsQuery);

        return response()->json([
            'category' => new CategoryResource($category),
            'items' => $resourceCollection->data,
            'pagination' => [
                'current_page' => $productsQuery->currentPage(),
                'last_page' => $productsQuery->lastPage(),
                'per_page' => $productsQuery->perPage(),
                'total' => $productsQuery->total(),
            ]
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        return $this->products($request);
    }

    /**
     * Get products on sale
     */
    public function onSale(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with(['category', 'media'])
            ->withStockInfo()
            // ->whereHas('activeSaleInfo') // TODO: Implement sale relation
            ->sort($request->input('sort'))
            ->paginate($request->input('per_page', 20));

        return ProductResource::collection($products);
    }

    /**
     * Get featured products for homepage: best sellers and new arrivals
     */
    public function featured(Request $request): JsonResponse
    {
        $bestSellers = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with(['category', 'media'])
            ->withStockInfo()
            ->inRandomOrder()
            ->limit(10)
            ->get();

        $newArrivals = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with(['category', 'media'])
            ->withStockInfo()
            ->latest('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'best_sellers' => ProductResource::collection($bestSellers)->response()->getData(true)['data'],
                'new_arrivals' => ProductResource::collection($newArrivals)->response()->getData(true)['data'],
            ]
        ]);
    }

    /**
     * Get all categories
     */
    public function categories(): AnonymousResourceCollection
    {
        $categories = Category::with('media')
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Get featured categories
     */
    public function featuredCategories(): AnonymousResourceCollection
    {
        $categories = Category::with('media')
            ->where('is_featured', true)
            ->first();
            // Simplified return for now, usually collection

        return CategoryResource::collection(
            Category::with('media')->where('is_featured', true)->take(8)->get()
        );
    }

    /**
     * Get filters for sidebar (Aggregations)
     * - Returns price_range { min, max }
     * - Returns sort options
     * - Returns filter options grouped by filter name
     * - Handles sale prices via PriceCalculationService
     */
    public function filters(Request $request): JsonResponse
    {
        $priceCalc = app(\App\Services\Ecommerce\PriceCalculationService::class);

        // Build product query
        $productsQuery = Product::query()
            ->purchasable()
            ->whereNull('parent_id');

        if ($request->filled('category')) {
            $category = Category::where('url', $request->input('category'))->first();
            if ($category) {
                $productsQuery->where('category_id', $category->id);
            }
        }

        $products = $productsQuery->with(['availableStocks' => function ($q) {
            $q->orderBy('priority')->orderBy('created_at');
        }])->get();

        $productIds = $products->pluck('id')->toArray();
        $activeSales = $this->getActiveSalesForProducts($productIds);

        // Calculate min/max price considering sales
        $allPrices = [];
        foreach ($products as $product) {
            foreach ($product->availableStocks as $stock) {
                if (!$stock->inStock()) continue;

                $basePrice = $priceCalc->getStockPrice($stock);
                $finalPrice = $basePrice;

                // Apply active sale if exists
                $sale = $activeSales->get($product->id);
                if ($sale && $sale['price'] > 0) {
                    $finalPrice = $sale['price'];
                }

                if ($finalPrice > 0) {
                    $allPrices[] = $finalPrice;
                }
            }
        }

        $minPrice = !empty($allPrices) ? min($allPrices) : 0;
        $maxPrice = !empty($allPrices) ? max($allPrices) : 0;

        // Sort Options
        $sortOptions = [
            ['label' => 'Popularity', 'value' => 'popularity'],
            ['label' => 'Newest First', 'value' => 'latest'],
            ['label' => 'Price: Low to High', 'value' => 'price_asc'],
            ['label' => 'Price: High to Low', 'value' => 'price_desc'],
            ['label' => 'Name: A to Z', 'value' => 'name_asc'],
        ];

        // Filter Options (Color, Size, etc.)
        $filterOptions = Filter::query()
            ->with(['options' => function ($query) use ($request) {
//                $query->whereHas('products', function ($q) use ($request) {
//                    $q->where('products.status', 'published')
//                        ->whereNull('products.parent_id');
//
//                    if ($request->filled('category')) {
//                        $category = Category::where('url', $request->input('category'))->first();
//                        if ($category) {
//                            $q->where('products.category_id', $category->id);
//                        }
//                    }
//                })
//                    ->withCount(['products' => function ($q) use ($request) {
//                    $q->where('products.status', 'published')
//                        ->whereNull('products.parent_id');
//
//                    if ($request->filled('category')) {
//                        $category = Category::where('url', $request->input('category'))->first();
//                        if ($category) {
//                            $q->where('products.category_id', $category->id);
//                        }
//                    }
//                }]);
            }])
//            ->whereHas('options.products', function ($q) use ($request) {
//                $q->where('products.status', 'published')->whereNull('products.parent_id');
//
//                if ($request->filled('category')) {
//                    $category = Category::where('url', $request->input('category'))->first();
//                    if ($category) {
//                        $q->where('products.category_id', $category->id);
//                    }
//                }
//            })
            ->get()
            ->map(function ($filter) {
                return [
                    'name' => $filter->name,
                    'options' => $filter->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'value' => $option->value,
                            'swatch' => $option->swatch_value,
                            'count' => $option->products_count,
                        ];
                    })->values(),
                ];
            });




        return response()->json([
            'success' => true,
            'data' => [
                'price_range' => [
                    'min' => (int) $minPrice,
                    'max' => (int) $maxPrice,
                ],
                'sort_options' => $sortOptions,
                'filter_options' => $filterOptions,
            ]
        ]);
    }

    /**
     * Get active sales for products
     */
    private function getActiveSalesForProducts(array $productIds): \Illuminate\Support\Collection
    {
        return \App\Models\Ecommerce\SaleProduct::query()
            ->whereIn('product_id', $productIds)
            ->where('starts_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ends_till')->orWhere('ends_till', '>=', now());
            })
            ->orderBy('sort_order')
            ->get()
            ->keyBy('product_id')
            ->map(function ($saleProduct) {
                return [
                    'product_id' => $saleProduct->product_id,
                    'price' => $saleProduct->sale_price ?? 0,
                    'discount_amount' => $saleProduct->discount_amount ?? 0,
                ];
            });
    }
}
