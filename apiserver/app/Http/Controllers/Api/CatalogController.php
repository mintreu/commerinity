<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\CategoryResource;
use App\Http\Resources\Ecommerce\FilterResource;
use App\Http\Resources\Ecommerce\ProductResource;
use App\Http\Resources\Ecommerce\ProductDetailResource;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogController extends Controller
{
    /**
     * Get all products with filtering
     */
    public function products(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with(['category', 'media'])
            // Load stock for price calculation and availability
            ->withStockInfo()
            // Apply Scopes
            ->search($request->input('search'))
            ->byCategory($request->input('category'))
            // Filter options logic (TBD if needed via scope, currently basic)
            // Price range filter
            ->byPrice(
                $request->input('min_price') ? (int) $request->input('min_price') : null,
                $request->input('max_price') ? (int) $request->input('max_price') : null
            )
            ->sort($request->input('sort'))
            ->paginate($request->input('per_page', 20));

        // Pass context if needed via additional data wrapper or transform
        return ProductResource::collection($products);
    }

    /**
     * Get single product detail
     */
    public function show(Request $request, Product $product): ProductDetailResource|JsonResponse
    {
        if ($product->status->value !== 'published') {
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
            'activeSaleInfo'
        ]);

        return new ProductDetailResource($product);
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
     * Get featured products (simplified)
     */
    public function featured(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->purchasable()
            ->whereNull('parent_id')
            ->with(['category', 'media'])
            ->withStockInfo()
            ->inRandomOrder() // Or a featured flag if added later
            ->limit(10)
            ->get();

        return ProductResource::collection($products);
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
     * - Returns min/max price
     * - Returns categories
     * - Returns filter options
     */
    public function filters(Request $request): JsonResponse
    {
        // 1. Calculate Price Range from Stock
        $priceStats = \Illuminate\Support\Facades\DB::table('product_stocks')
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->where('products.status', 'published')
            ->selectRaw('MIN(COALESCE(product_stocks.price, product_stocks.landing_cost * (1 + product_stocks.profit_margin/100))) as min_price')
            ->selectRaw('MAX(COALESCE(product_stocks.price, product_stocks.landing_cost * (1 + product_stocks.profit_margin/100))) as max_price')
            ->first();

        // 2. Categories
        $categories = Category::whereNull('parent_id')
            ->select('id', 'name', 'url', 'slug')
            ->withCount('products')
            ->get();

        return response()->json([
            'min_price' => (int) $priceStats->min_price,
            'max_price' => (int) $priceStats->max_price,
            'categories' => $categories,
            // 'attributes' => ... (To be implemented via FilterGroup)
        ]);
    }
}
