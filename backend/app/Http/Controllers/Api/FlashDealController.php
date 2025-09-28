<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Promotion\SaleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\LaravelCommerinity\Models\Sale;
use Mintreu\LaravelCommerinity\Models\SaleProduct;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class FlashDealController extends Controller
{
    /**
     * Get all active flash deals
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $runningSales = Sale::with([
            'sale_products.product',
            'sale_products.product.media' => fn($query) => $query->where('collection_name','displayImage'),
            'sale_products.product.categories' => fn($query) => $query->where('parent_id',null),
        ])
            ->where(function($query)  {
                $query->where('name', 'LIKE', '%flash%')
                    ->orWhere('name', 'LIKE', '%deal%')
                    ->orWhere('description', 'LIKE', '%flash%')
                    ->orWhere('description', 'LIKE', '%deal%')
                    ->orWhereRaw('DATEDIFF(ends_till, starts_from) <= 3');
            })
            ->whereDate('starts_from', '<=', now()->toDateString())
            ->whereDate('ends_till', '>=', now()->toDateString())
            ->latest()
            ->paginate(30);

        dd($runningSales->first()->sale_products->first());


        return SaleResource::collection($runningSales);

        //return FlashDealResource::collection($flashDeals);
    }

    /**
     * Get flash deals statistics
     */
    public function getStats(): JsonResponse
    {
        // Get current active flash deals count
        $totalDeals = SaleProduct::query()
            ->whereHas('sale', function($query) {
                $query->where('name', 'LIKE', '%flash%')
                    ->orWhere('name', 'LIKE', '%deal%');
            })
            ->whereDate('starts_from', '<=', now())
            ->whereDate('ends_till', '>=', now())
            ->whereNotNull('product_id')
            ->whereHas('product')
            ->count();

        // Calculate average discount percentage using integer fields
        $avgDiscountData = SaleProduct::query()
            ->join('products', 'sale_products.product_id', '=', 'products.id')
            ->whereHas('sale', function($query) {
                $query->where('name', 'LIKE', '%flash%')
                    ->orWhere('name', 'LIKE', '%deal%');
            })
            ->whereDate('sale_products.starts_from', '<=', now())
            ->whereDate('sale_products.ends_till', '>=', now())
            ->whereNotNull('sale_products.product_id')
            ->selectRaw('
                AVG(
                    (sale_products.discount_amount / CAST(JSON_UNQUOTE(JSON_EXTRACT(products.price, "$.amount")) AS DECIMAL(10,2))) * 100
                ) as avg_discount
            ')
            ->first();

        $avgDiscount = round($avgDiscountData->avg_discount ?? 0);

        // Mock customers saved (you can implement real logic)
        $customersSaved = 125;

        return response()->json([
            'total_deals' => $totalDeals,
            'avg_discount' => $avgDiscount,
            'customers_saved' => $customersSaved,
        ]);
    }

    /**
     * Get flash deal categories with counts - using actual category images
     */
    public function getCategories(): JsonResponse
    {
        // Get category IDs and deal counts for categories that have flash deals
        $categoryDeals = DB::table('sale_products as sp')
            ->join('products as p', 'sp.product_id', '=', 'p.id')
            ->join('category_mappings as cm', function($join) {
                $join->on('p.id', '=', 'cm.categorized_id')
                    ->where('cm.categorized_type', Product::class);
            })
            ->join('sales as s', 'sp.sale_id', '=', 's.id')
            ->where(function($query) {
                $query->where('s.name', 'LIKE', '%flash%')
                    ->orWhere('s.name', 'LIKE', '%deal%');
            })
            ->whereDate('sp.starts_from', '<=', now())
            ->whereDate('sp.ends_till', '>=', now())
            ->where('p.status', PublishableStatusCast::PUBLISHED->value)
            ->whereNull('p.parent_id')
            ->groupBy('cm.category_id')
            ->select([
                'cm.category_id',
                DB::raw('COUNT(DISTINCT p.id) as deal_count')
            ])
            ->having('deal_count', '>', 0)
            ->pluck('deal_count', 'cm.category_id');

        // If no deals found, return empty array
        if ($categoryDeals->isEmpty()) {
            return response()->json([]);
        }

        // Get actual category models with their media (images)
        $categories = Category::whereIn('id', array_keys($categoryDeals->toArray()))
            ->where('status', true)
            ->where('is_visible_on_front', true)
            ->with(['media' => fn($q) => $q->where('collection_name', 'displayImage')])
            ->get()
            ->map(function($category) use ($categoryDeals) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'url' => $category->url,
                    'deal_count' => $categoryDeals[$category->id] ?? 0,
                    'image' => $category->getFirstMediaUrl('displayImage'), // Uses actual category image or fallback
                    'thumbnail' => $category->getFirstMediaUrl('displayImage'), // For consistency with other resources
                    'views' => $category->view_count,
                    'meta' => $category->meta_data,
                ];
            })
            ->sortByDesc('deal_count')
            ->values()
            ->take(12); // Limit to top 12 categories

        return response()->json($categories);
    }
}
