<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalogue\Product\ProductIndexResource;
use App\Models\Lifecycle\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelProductCatalogue\Models\ProductTier;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class ProductDisplayController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();

        $query = Product::select([
            'id','name','url','sku','view_count','type','status',
            'filter_group_id','reward_point','parent_id','price'
        ])
            ->where('type', ProductTypeCast::CONFIGURABLE)
            ->where('status', PublishableStatusCast::PUBLISHED)
            ->with([
                'media' => fn($q) => $q->where('collection_name', 'displayImage'),
                'categories' => fn($q) =>
                $request->has('categories')
                    ? $q->whereIn('url', (array) $request->get('categories'))
                    : $q->latest()
            ])
            ->whereHas('categories', fn($q) =>
            $request->has('categories')
                ? $q->whereIn('url', (array) $request->get('categories'))
                : $q
            );

        // Add wishlist boolean only for authenticated users
        if ($currentUser) {
            $userClass = get_class($currentUser);
            $userId = $currentUser->id;

            $query->addSelect([
                'is_wishlisted' => DB::table('product_wishlists')
                    ->select(DB::raw('CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END'))
                    ->where('product_id', DB::raw('products.id'))
                    ->where('authorable_type', $userClass)
                    ->where('authorable_id', $userId)
                    ->limit(1)
            ]);

            if ($currentUser->level_id) {
                $query->with([
                    'sales' => function ($query) use ($currentUser) {
                        $query
                            ->where('ends_till','>',now())
                            ->where('target_type', Level::class)
                            ->where('target_id', $currentUser->level_id)
                            ->orWhere(function ($query){
                                $query
                                    ->whereNull('target_type')
                                    ->whereNull('target_id');
                            })
                            ->orderBy('sale_price')->limit(1);
                    },
                ]);
            } else {
                $query->with([
                    'sales' => function ($query) {
                        $query
                            ->where('ends_till','>',now())
                            ->whereNull('target_type')
                            ->whereNull('target_id')
                            ->orderBy('sale_price')->limit(1);
                    },
                ]);
            }
        } else {
            $query->with([
                'sales' => function ($query) {
                    $query
                        ->where('ends_till','>',now())
                        ->whereNull('target_type')
                        ->whereNull('target_id')
                        ->orderBy('sale_price')->limit(1);
                },
            ]);
        }

        $query->withExists([
            'tiers as has_stock' => fn($q) => $q->InStock()
        ]);

        // YOUR WORKING PRICE FILTER - KEPT EXACTLY AS IS
        if (isset($request->price)) {
            $minPrice = isset($request->price['min']) ? $request->price['min'] * 100 : null;
            $maxPrice = isset($request->price['max']) ? $request->price['max'] * 100 : null;
            $query->where('price','>=',$minPrice)->where('price','<=',$maxPrice);

            $query->with([
                'cheapestTier' => fn($query) => $query->where('price','>=',$minPrice)->where('price','<=',$maxPrice)->limit(1)
            ]);
            $query->whereHas('cheapestTier',fn($query) =>  $query->where('price','>=',$minPrice)->where('price','<=',$maxPrice));
        } else {
            $query->with([
                'cheapestTier' => fn($query) =>  $query->orderBy('price')->limit(1)
            ]);
        }

        $query->withAvg('engagements', 'rating')
            ->withAvg('engagements', 'helpful_votes')
            ->withCount('engagements as review_count');

        // Apply all additional filters
        $this->applySearchFilter($query, $request);
        $this->applyStockFilter($query, $request);
        $this->applyOfferFilter($query, $request);
        $this->applyRatingFilter($query, $request);
        $this->applyVendorFilter($query, $request);
        $this->applyFilterOptionsFilters($query, $request);
        $this->applySorting($query, $request);



        return ProductIndexResource::collection($query->paginate(12));
    }

    // NEW: Search functionality
    private function applySearchFilter($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('short_description', 'LIKE', "%{$search}%");
            });
        }
    }

    // NEW: Stock filtering
    private function applyStockFilter($query, Request $request): void
    {
        if ($request->boolean('in_stock')) {
            $query->whereHas('tiers', fn($t) => $t->where('in_stock', true));
        }
    }

    // NEW: Offer/Sale filtering
    private function applyOfferFilter($query, Request $request): void
    {
        if ($request->boolean('offer')) {
            $query->whereHas('sales', fn($s) =>
            $s->whereDate('starts_from', '<=', now())
                ->where(fn($q) =>
                $q->whereNull('ends_till')
                    ->orWhereDate('ends_till', '>=', now())
                )
            );
        }
    }

    // NEW: Rating filtering
    private function applyRatingFilter($query, Request $request): void
    {
        if ($request->filled('min_rating')) {
            $minRating = (float) $request->input('min_rating');
            $query->havingRaw('engagements_avg_rating >= ?', [$minRating]);
        }
    }

    // NEW: Vendor/Tenant filtering
    private function applyVendorFilter($query, Request $request): void
    {
        if ($request->filled('vendor')) {
            $vendorIds = (array) $request->input('vendor');
            $query->where(function ($q) use ($vendorIds) {
                foreach ($vendorIds as $vendorId) {
                    $q->orWhere(function ($subQ) use ($vendorId) {
                        $subQ->where('tenant_type', 'LIKE', '%Vendor%')
                            ->where('tenant_id', $vendorId);
                    });
                }
            });
        }
    }

    // FIXED: Sorting method
    private function applySorting($query, Request $request): void
    {
        $sort = $request->input('sort', []);

        if (!is_array($sort) || empty($sort)) {
            $query->latest();
            return;
        }

        $key = array_key_first($sort);
        $direction = strtolower($sort[$key] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        switch ($key) {
            case 'popularity':
                $query->orderBy('view_count', $direction);
                break;

            case 'latest':
                $query->latest();
                break;

            case 'pricelow2high':
                $query->orderBy('price', 'asc'); // FIXED: Use simple price ordering
                break;

            case 'pricehigh2low':
                $query->orderBy('price', 'desc'); // FIXED: Use simple price ordering
                break;

            case 'rating':
                $query->orderBy('engagements_avg_rating', $direction);
                break;

            case 'name':
                $query->orderBy('name', $direction);
                break;

            default:
                $query->latest();
        }
    }

    /**
     * Apply filter options efficiently
     */
    private function applyFilterOptionsFilters($query, Request $request): void
    {
        $filters = $request->input('filters', []);

        if (!is_array($filters) || empty($filters)) {
            return;
        }

        foreach ($filters as $filterName => $values) {
            $values = array_values(array_filter((array)$values, fn($v) => $v !== null && $v !== ''));

            if (empty($values)) {
                continue;
            }

            $query->whereHas('filterOptions', function ($fo) use ($filterName, $values) {
                $fo->whereHas('filter', fn($f) => $f->where('name', $filterName))
                    ->whereIn('value', $values);
            });
        }
    }
}
