<?php

namespace App\Http\BackUp;


use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductIndexResource;
use App\Http\Resources\Product\ProductResource;
use App\Scopes\CategoryScope;
use App\Scopes\FilterScope;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class ProductController extends Controller
{


    protected function scopes()
    {
        return [
            'filters' => new FilterScope,
            'categories' => new CategoryScope,
        ];
    }


    public function getFilterScopes(?string $scope_name = null): array
    {
        $allScopes = [
            'filters' => new FilterScope(),
            'categories' => new CategoryScope(),
        ];

        return is_null($scope_name) ? $allScopes : $allScopes[$scope_name];
    }


    public function getFilterOptions()
    {
        $filterGroups = FilterGroup::with([
            'filters.options'
        ])->get();

        $groupBag = [];
        foreach ($filterGroups as $group) {
            if ($group->filters->count()) {
                foreach ($group->filters as $filter) {
                    // Map over options and ensure swatch_value is not null
                    $options = $filter->options->map(function ($option) {
                        if (is_null($option->swatch_value)) {
                            $option->swatch_value = Str::ucfirst($option->value);
                        }
                        return $option;
                    });

                    $groupBag[$group->name][$filter->name] = $options->pluck('swatch_value', 'value')->toArray();

                    // Remove or comment this dd for production use
                    // dd($groupBag, $filter->options);
                }
            }
        }

        return $groupBag;
    }



    public function getSortingOptions(): array
    {
        return [

            [
                'name' => 'popularity',
                'value' => 'view_count',
                'direction' => 'desc',
            ],
            [
                'name' => 'latest',
                'value' => 'created_at',
                'direction' => 'desc',
            ],
            [
                'name' => 'pricelow2high',
                'value' => 'price',
                'direction' => 'asc',
            ],
            [
                'name' => 'pricehigh2low',
                'value' => 'price',
                'direction' => 'desc',
            ],
        ];
    }

    public function getCurrentSort(Request $request): array
    {
        $allOptions = collect($this->getSortingOptions());
        if ($request->sort) {
            $sortKey = key($request->sort);

            $matchingSort = $allOptions->first(function ($sort) use ($sortKey) {
                return $sort['name'] === $sortKey;
            });

            return $matchingSort ?? [];
        }

        return $defaultSort = $allOptions->first(function ($sort) {
            return $sort['value'] === 'created_at';
        });
    }



    public function getAllSimpleProducts()
    {
        $products = Product::with([
            'media' => fn($query) => $query->where('collection_name','displayImage')
        ])
            ->where('type',ProductTypeCast::SIMPLE)
            ->where('status',PublishableStatusCast::PUBLISHED)
            ->paginate();

        return ProductIndexResource::collection($products);
    }



    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {

        $product->load([
            //'filterGroup.filters.options',
            'media' => fn($query) => $query->where('collection_name','bannerImage'),
            'filterOptions.filter',

            'tiers',
            'categories',
            'sales' => fn($query) => $query
                ->with('sale')
                ->whereDate('starts_from', '<=', now())
               // ->whereDate('ends_till', '>=', now())
            ,
        ]);

        if (!is_null($product->parent_id))
        {
            $product->load([
                'parent.variants.media',
                'parent.variants.filterOptions.filter',
            ]);
        }else{
            $product->load([
                'variants.media',
                'variants.filterOptions.filter',
            ]);
        }



        // Load related products using siblingsAndSelf
        if ($product->category) {
            $siblings = $product->siblings()
                ->with(['media'])
                ->limit(4)
                ->get();

            $product->related_products = $siblings;
        }

        return new ProductResource($product);
    }


    /**
     * Update product view count
     */
    public function updateViews(Product $product)
    {
        $product->increment('view_count');

        return response()->json([
            'success' => true,
            'views' => $product->view_count,
        ]);
    }



    public function topSuggestProduct(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $products = Product::with([
            'media' => fn($query) => $query->where('collection_name','displayImage')
        ])
            ->where('type',ProductTypeCast::SIMPLE)->limit(20)->get();

        return ProductIndexResource::collection($products);
    }




}
