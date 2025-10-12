<?php

namespace App\Http\Controllers\Api\Product;

use App\Casts\OrderStatusCast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Catalogue\Product\ProductIndexResource;
use App\Http\Resources\Catalogue\Product\ProductResource;
use App\Models\Lifecycle\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class ProductDisplayController extends Controller
{



    public function show(Product $product,Request $request)
    {

        if (!in_array($product->status->value,[PublishableStatusCast::PUBLISHED->value,PublishableStatusCast::ARCHIVED->value]))
        {
            return response()->json([
               'status' => false,
               'message' => 'product not found!'
            ]);
        }


        $currentUser = $request->user();

        $query = $product->load([
            'media' => fn($q) => $q->where('collection_name', 'displayImage')->where('collection_name', 'bannerImage'),
            'categories' => fn($q) => $q->latest(),
            'engagements',
            'filterOptions.filter'
        ]);

        if ($currentUser)
        {
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
                $query->load([
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
                $query->load([
                    'sales' => function ($query) {
                        $query
                            ->where('ends_till','>',now())
                            ->whereNull('target_type')
                            ->whereNull('target_id')
                            ->orderBy('sale_price')->limit(1);
                    },
                ]);
            }



        }else{
            $query->load([
                'sales' => function ($query) {
                    $query
                        ->where('ends_till','>',now())
                        ->whereNull('target_type')
                        ->whereNull('target_id')
                        ->orderBy('sale_price')->limit(1);
                },
            ]);
        }

        $query->loadExists([
            'tiers as has_stock' => fn($q) => $q->InStock()
        ]);

        $query->load([
            'tiers' => fn($query) =>  $query->orderBy('price')
        ]);

        $query->loadAvg('engagements', 'rating')
            ->loadAvg('engagements', 'helpful_votes')
            ->loadCount('engagements as review_count');


        if ($product->type == ProductTypeCast::CONFIGURABLE)
        {
            $query->load([
                'variants'
            ]);
        }elseif ($product->type == ProductTypeCast::SIMPLE)
        {
            $query->load([
               'parent.variants'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'product not processable' // we don't sale wholesale products from this api
            ]);
        }


        return ProductResource::make($product);

    }





    public function topSaleProduct(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query  = Product::select([
            'id','name','url','sku','view_count','type','status',
            'filter_group_id','reward_point','parent_id','price'
        ])->with([
            'media' => fn($query) => $query->where('collection_name', 'displayImage'),
            'categories'
        ])
            ->withCount([
                'orders as confirmed_orders_count' => function ($query) {
                    $query->where('status', OrderStatusCast::CONFIRM->value);
                },
            ])
            ->where('type', ProductTypeCast::SIMPLE)
            ->orderByDesc('confirmed_orders_count');


       // $query = $this->WithCommon($query,$request);


        $products = $query->limit(20)
            ->get();

        return ProductIndexResource::collection($products);
    }


    public function trendingProducts(Request $request)
    {
        $query = Product::select([
            'id','name','url','sku','view_count','type','status',
            'filter_group_id','reward_point','parent_id','price'
        ])->with([
            'media' => fn($query) => $query->where('collection_name', 'displayImage'),
            'categories'
        ])->where('type', ProductTypeCast::CONFIGURABLE->value);

        $query = $this->WithCommon($query,$request);

        $products =$query->orderByDesc('view_count')
            ->limit(20)
            ->get();


        return ProductIndexResource::collection($products);
    }



    protected function WithCommon($query,$request)
    {
        $query->whereHas('categories');

        $currentUser = $request->user();
        if ($currentUser?->level_id) {
            $query->with([
                'sales' => function ($query) use ($currentUser) {
                    $query
                        ->where('ends_till','>',now())
                        ->where('target_type', Level::class)
                        ->where('target_id', $currentUser?->level_id)
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

        $query->withExists([
            'tiers as has_stock' => fn($q) => $q->InStock()
        ]);

        $query->with([
            'cheapestTier' => fn($query) =>  $query->orderBy('price')->limit(1)
        ]);

        $query->withAvg('engagements', 'rating')
            ->withAvg('engagements', 'helpful_votes')
            ->withCount('engagements as review_count');


        return $query;
    }

}
