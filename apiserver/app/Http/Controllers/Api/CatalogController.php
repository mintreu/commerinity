<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\CategoryResource;
use App\Http\Resources\Ecommerce\ProductDetailResource;
use App\Http\Resources\Ecommerce\ProductResource;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Filter;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\SaleProduct;
use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
        $this->applyStockContext($request);
        $query = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with(['category', 'media'])
            ->withStockInfo()
            ->search($request->input('search'))
            ->byCategory($request->input('category'))
            ->byPrice(
                $request->input('min_price') ? (int) $request->input('min_price') * 100 : null,
                $request->input('max_price') ? (int) $request->input('max_price') * 100 : null
            );
        $query->with(['availableStocks' => fn ($q) => $q->with('address')->orderBy('created_at')]);

        $this->applyOptionFilters($query, $request);

        $products = $query
            ->sort($request->input('sort'))
            ->paginate($request->input('per_page', 20));

        $this->applyActiveSalesToProducts(
            $products->getCollection(),
            auth('sanctum')->user()
        );

        return ProductResource::collection($products);
    }

    /**
     * Get single product detail
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        $this->applyStockContext($request);
        if ($product->status->value !== 'Published') {
            return response()->json(['message' => 'Product not available'], 404);
        }

        $product->load([
            'category',
            'media',
            'variants' => function ($q) {
                $q->with(['media', 'availableStocks' => fn ($sq) => $sq->with('address')->orderBy('created_at')])->purchasable();
            },
            'availableStocks' => fn ($q) => $q->with('address')->orderBy('created_at'),
            'filterOptions.filter',
        ]);

        $allProducts = collect([$product])->merge($product->variants);
        $this->applyActiveSalesToProducts($allProducts, auth('sanctum')->user());

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
        $this->applyStockContext($request);
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
                $request->input('min_price') ? (int) $request->input('min_price') * 100 : null,
                $request->input('max_price') ? (int) $request->input('max_price') * 100 : null
            )
            ->sort($request->input('sort'))
            ->paginate($perPage, ['*'], 'page', $page);
        $productsQuery->getCollection()->load(['availableStocks' => fn ($q) => $q->with('address')->orderBy('created_at')]);

        $this->applyActiveSalesToProducts(
            $productsQuery->getCollection(),
            auth('sanctum')->user()
        );

        $resourceCollection = ProductResource::collection($productsQuery);

        return response()->json([
            'category' => new CategoryResource($category),
            'items' => $resourceCollection->response()->getData(true)['data'],
            'pagination' => [
                'current_page' => $productsQuery->currentPage(),
                'last_page' => $productsQuery->lastPage(),
                'per_page' => $productsQuery->perPage(),
                'total' => $productsQuery->total(),
            ],
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
        $this->applyStockContext($request);
        $products = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with(['category', 'media'])
            ->withStockInfo()
            // ->whereHas('activeSaleInfo') // TODO: Implement sale relation
            ->sort($request->input('sort'))
            ->paginate($request->input('per_page', 20));
        $products->getCollection()->load(['availableStocks' => fn ($q) => $q->with('address')->orderBy('created_at')]);

        $this->applyActiveSalesToProducts(
            $products->getCollection(),
            auth('sanctum')->user()
        );

        return ProductResource::collection($products);
    }

    /**
     * Get featured products for homepage: best sellers and new arrivals
     */
    public function featured(Request $request): JsonResponse
    {
        $this->applyStockContext($request);
        $bestSellers = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with(['category', 'media'])
            ->withStockInfo()
            ->inRandomOrder()
            ->limit(10)
            ->get();
        $bestSellers->load(['availableStocks' => fn ($q) => $q->with('address')->orderBy('created_at')]);

        $newArrivals = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with(['category', 'media'])
            ->withStockInfo()
            ->latest('created_at')
            ->limit(10)
            ->get();
        $newArrivals->load(['availableStocks' => fn ($q) => $q->with('address')->orderBy('created_at')]);

        $this->applyActiveSalesToProducts(
            $bestSellers->concat($newArrivals),
            auth('sanctum')->user()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'best_sellers' => ProductResource::collection($bestSellers)->response()->getData(true)['data'],
                'new_arrivals' => ProductResource::collection($newArrivals)->response()->getData(true)['data'],
            ],
        ]);
    }

    /**
     * Get all categories
     */
    public function categories(): AnonymousResourceCollection
    {
        $categories = Category::with(['media', 'children.media'])
            ->withCount('products')
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
        return CategoryResource::collection(
            Category::with(['media', 'children.media'])
                ->withCount('products')
                ->where('is_featured', true)
                ->take(8)
                ->get()
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
        $this->applyStockContext($request);
        $productsQuery = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->search($request->input('search'))
            ->byCategory($request->input('category'))
            ->byPrice(
                $request->input('min_price') ? (int) $request->input('min_price') * 100 : null,
                $request->input('max_price') ? (int) $request->input('max_price') * 100 : null
            );

        $this->applyOptionFilters($productsQuery, $request);

        $products = $productsQuery->with(['availableStocks' => function ($q) {
            $q->orderBy('priority')->orderBy('created_at');
        }])->get();

        $productIds = $products->pluck('id')->filter()->values();
        $variantIds = Product::query()
            ->whereIn('parent_id', $productIds)
            ->pluck('id')
            ->filter()
            ->values();
        $allProductIds = $productIds->merge($variantIds)->unique()->values();

        // Calculate min/max price considering sales
        $allPrices = $products
            ->map(fn ($product) => $product->getDisplayPrice())
            ->filter(fn ($price) => $price > 0)
            ->values()
            ->all();

        $minPrice = ! empty($allPrices) ? min($allPrices) : 0;
        $maxPrice = ! empty($allPrices) ? max($allPrices) : 0;

        // Sort Options
        $sortOptions = [
            ['label' => 'Popularity', 'value' => 'popularity'],
            ['label' => 'Newest First', 'value' => 'latest'],
            ['label' => 'Price: Low to High', 'value' => 'price_asc'],
            ['label' => 'Price: High to Low', 'value' => 'price_desc'],
            ['label' => 'Name: A to Z', 'value' => 'name_asc'],
        ];

        $filterGroupIds = $products->pluck('filter_group_id')
            ->filter()
            ->unique()
            ->values();

        $filterOptionsQuery = Filter::query()
            ->with(['options' => function ($query) use ($allProductIds) {
                $query->whereHas('products', function ($q) use ($allProductIds) {
                    $q->whereIn('products.id', $allProductIds);
                })->withCount(['products' => function ($q) use ($allProductIds) {
                    $q->whereIn('products.id', $allProductIds);
                }]);
            }]);

        if ($filterGroupIds->isNotEmpty()) {
            $filterOptionsQuery->whereHas('groups', function ($q) use ($filterGroupIds) {
                $q->whereIn('filter_groups.id', $filterGroupIds);
            });
        }

        $filterOptions = $filterOptionsQuery
            ->get()
            ->map(function ($filter) {
                $options = $filter->options
                    ->filter(fn ($option) => (int) $option->products_count > 0)
                    ->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'value' => $option->value,
                            'swatch' => $option->swatch_value,
                            'count' => (int) $option->products_count,
                        ];
                    })
                    ->values();

                return [
                    'name' => $filter->name,
                    'options' => $options,
                ];
            })
            ->filter(fn ($filter) => $filter['options']->isNotEmpty())
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'price_range' => [
                    'min' => (int) $minPrice,
                    'max' => (int) $maxPrice,
                ],
                'sort_options' => $sortOptions,
                'filter_options' => $filterOptions,
            ],
        ]);
    }

    /**
     * Get active sales for products
     */
    private function resolveSaleContext(?User $user): array
    {
        if (! $user instanceof User) {
            return [
                'user_id' => null,
                'user_type' => null,
                'stage_id' => null,
                'level_id' => null,
            ];
        }

        $subscription = UserSubscription::getActiveForUser($user->id);

        return [
            'user_id' => $user->id,
            'user_type' => $user->type?->value,
            'stage_id' => $subscription?->stage_id,
            'level_id' => $subscription?->current_level_id
                ?? $subscription?->level_id
                ?? $user->level_id,
        ];
    }

    private function applyActiveSalesToProducts($products, ?User $user): void
    {
        $productIds = $products->pluck('id')->filter()->values()->all();
        $activeSales = $this->getActiveSalesForProducts($productIds, $user);

        foreach ($products as $product) {
            $product->setRelation('activeSaleInfo', $activeSales[$product->id] ?? null);
        }
    }

    private function getActiveSalesForProducts(array $productIds, ?User $user = null): array
    {
        if (empty($productIds)) {
            return [];
        }

        $context = $this->resolveSaleContext($user);
        $userType = $context['user_type'];
        $userStageId = $context['stage_id'];
        $userLevelId = $context['level_id'];

        $saleProductQuery = SaleProduct::query()
            ->active()
            ->whereIn('product_id', $productIds)
            ->ordered()
            ->where(function ($q) use ($context, $userStageId, $userLevelId): void {
                $q->whereNull('target_type')
                    ->orWhereIn('target_type', [Category::class, Product::class]);

                if ($context['user_id']) {
                    $q->orWhere(function ($targetQuery) use ($context): void {
                        $targetQuery->where('target_type', User::class)
                            ->where('target_id', $context['user_id']);
                    });

                    if ($userStageId) {
                        $q->orWhere(function ($targetQuery) use ($userStageId): void {
                            $targetQuery->where('target_type', Stage::class)
                                ->where('target_id', $userStageId);
                        });
                    }

                    if ($userLevelId) {
                        $q->orWhere(function ($targetQuery) use ($userLevelId): void {
                            $targetQuery->where('target_type', Level::class)
                                ->where('target_id', $userLevelId);
                        });
                    }
                }
            })
            ->where(function ($q) use ($userType): void {
                $q->whereNull('sale_id')
                    ->orWhereHas('sale', function ($saleQuery) use ($userType): void {
                        $saleQuery->where(function ($userTypeQuery) use ($userType): void {
                            $userTypeQuery->whereNull('target_user_types')
                                ->orWhereJsonLength('target_user_types', 0);

                            if ($userType) {
                                $userTypeQuery->orWhereJsonContains('target_user_types', $userType);
                            }
                        });
                    });
            })
            ->with('sale');

        $activeSales = [];

        foreach ($saleProductQuery->get() as $saleProduct) {
            if (! isset($activeSales[$saleProduct->product_id]) && $saleProduct->isActive()) {
                $activeSales[$saleProduct->product_id] = [
                    'type' => 'product_sale',
                    'sale_product' => $saleProduct,
                    'name' => $saleProduct->sale?->name ?? 'Special Offer',
                    'ends_at' => $saleProduct->ends_till ?? $saleProduct->sale?->ends_till,
                ];
            }
        }

        return $activeSales;
    }

    private function applyStockContext(Request $request): void
    {
        if (! $request->filled('shipping_address_id')) {
            return;
        }

        $user = auth('sanctum')->user();
        if (! $user instanceof User) {
            return;
        }

        $address = $user->addresses()
            ->where('uuid', $request->input('shipping_address_id'))
            ->first();

        if (! $address) {
            return;
        }

        $request->attributes->set('stock_context', $address);
    }

    private function applyOptionFilters(Builder $query, Request $request): void
    {
        if (! $request->filled('filters')) {
            return;
        }

        $filters = json_decode($request->input('filters'), true);
        if (! is_array($filters)) {
            return;
        }

        foreach ($filters as $filterName => $optionIds) {
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
