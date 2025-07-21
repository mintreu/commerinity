<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Scopes\CategoryScope;
use App\Scopes\FilterScope;
use Illuminate\Http\Request;

class ProductController extends Controller
{


    public function getFilterScopes(?string $scope_name = null): array
    {
        $allScopes = [
            'filters' => new FilterScope,
            'categories' => new CategoryScope,
        ];

        return is_null($scope_name) ? $allScopes : $allScopes[$scope_name];
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

    protected function scopes()
    {
        return [
            'filters' => new FilterScope,
            'categories' => new CategoryScope,
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load([
            'filterGroup.filters.options',
            'productDisplay',
            'productGallery',
            'filterOptions',
            'filterOptions.filter',
            'variants',
            'bulkPricing',
            'category',
        ]);

        // Load related products using siblingsAndSelf
        if ($product->category) {
            $siblings = $product->siblings()
                ->with(['productDisplay', 'productGallery'])
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







}
