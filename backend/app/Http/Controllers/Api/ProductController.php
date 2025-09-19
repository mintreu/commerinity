<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductIndexResource;
use App\Http\Resources\Product\ProductResource;
use App\Scopes\CategoryScope;
use App\Scopes\FilterScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\Toolkit\Casts\PublishableStatusCast;
use Mintreu\LaravelCategory\Models\Category;

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

    // UPDATED: now accepts ?category={category-url} and returns only in-use options for that category tree
//    public function getFilterOptions(Request $request)
//    {
//        $categoryUrl = $request->query('category');
//
//        if ($categoryUrl) {
//            $category = \Mintreu\LaravelCategory\Models\Category::with('descendants')
//                ->where('url', $categoryUrl)
//                ->first();
//
//            if ($category) {
//                $categoryIds = $category->descendants->pluck('id')->push($category->id);
//
//                // All parent (non-variant) published products under this category tree
//                $productIds = \Mintreu\LaravelProductCatalogue\Models\Product::whereNull('parent_id')
//                    ->where('status', \Mintreu\Toolkit\Casts\PublishableStatusCast::PUBLISHED)
//                    ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds))
//                    ->pluck('id');
//
//                if ($productIds->isNotEmpty()) {
//                    // NOTE: use correct pivot table from Product::filterOptions relation: 'product_filter_options'
//                    $optionIds = \Illuminate\Support\Facades\DB::table('product_filter_options')
//                        ->whereIn('product_id', $productIds)
//                        ->pluck('filter_option_id')
//                        ->unique();
//
//                    // Load groups/filters with only the options that are actually used in this category
//                    $filterGroups = \Mintreu\LaravelProductCatalogue\Models\FilterGroup::with([
//                        'filters.options' => fn($q) => $q->whereIn('id', $optionIds),
//                    ])->get();
//
//                    $groupBag = [];
//                    foreach ($filterGroups as $group) {
//                        if ($group->filters->count()) {
//                            foreach ($group->filters as $filter) {
//                                if ($filter->options->count() === 0) {
//                                    continue; // skip filters with no options in this category
//                                }
//
//                                // Keep same shape: value => swatch_value (label), defaulting when null
//                                $options = $filter->options->map(function ($option) {
//                                    $swatch = $option->swatch_value ?? \Illuminate\Support\Str::ucfirst($option->value);
//                                    return $swatch;
//                                })->pluck(null, 'value')->toArray();
//
//                                $groupBag[$group->name][$filter->name] = $options;
//                            }
//                        }
//                    }
//
//                    return $groupBag;
//                }
//
//                // If no products, return an empty set so the sidebar is clean
//                return [];
//            }
//        }
//
//        // Fallback: original global filters (unchanged)
//        $filterGroups = \Mintreu\LaravelProductCatalogue\Models\FilterGroup::with([
//            'filters.options'
//        ])->get();
//
//        $groupBag = [];
//        foreach ($filterGroups as $group) {
//            if ($group->filters->count()) {
//                foreach ($group->filters as $filter) {
//                    $options = $filter->options->map(function ($option) {
//                        if (is_null($option->swatch_value)) {
//                            $option->swatch_value = \Illuminate\Support\Str::ucfirst($option->value);
//                        }
//                        return $option;
//                    });
//
//                    $groupBag[$group->name][$filter->name] = $options->pluck('swatch_value', 'value')->toArray();
//                }
//            }
//        }
//
//        return $groupBag;
//    }



    public function getFilterOptions(Request $request)
    {
        $categoryUrl = $request->query('category');

        if ($categoryUrl) {
            $category = \Mintreu\LaravelCategory\Models\Category::with('descendants')
                ->where('url', $categoryUrl)
                ->first();

            if ($category) {
                $categoryIds = $category->descendants->pluck('id')->push($category->id);

                // All parent (non-variant) published products under this category tree
                $products = \Mintreu\LaravelProductCatalogue\Models\Product::query()
                    ->whereNull('parent_id')
                    ->where('status', \Mintreu\Toolkit\Casts\PublishableStatusCast::PUBLISHED)
                    ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds))
                    ->select(['id', 'filter_group_id'])
                    ->get();

                if ($products->isEmpty()) {
                    return []; // no products -> no filters to show
                }

                // Limit to groups used by these products
                $filterGroupIds = $products->pluck('filter_group_id')->filter()->unique()->values();

                if ($filterGroupIds->isEmpty()) {
                    return []; // products have no filter group assigned
                }

                // Limit options to those that are used by these products (correct pivot: product_filter_options)
                $productIds = $products->pluck('id');
                $optionIds = \Illuminate\Support\Facades\DB::table('product_filter_options')
                    ->whereIn('product_id', $productIds)
                    ->pluck('filter_option_id')
                    ->unique();

                if ($optionIds->isEmpty()) {
                    return []; // no options used -> empty sidebar
                }

                // Load only the groups used by products, keep only options used in this category
                $filterGroups = \Mintreu\LaravelProductCatalogue\Models\FilterGroup::query()
                    ->whereIn('id', $filterGroupIds)
                    ->with([
                        'filters.options' => fn($q) => $q->whereIn('id', $optionIds),
                    ])->get();

                // Prefer a single group whose slug matches the category URL for a crisp, relevant sidebar
                $categorySlug = \Illuminate\Support\Str::slug($category->url ?? $category->name ?? '');
                $matching = $filterGroups->filter(fn($g) => \Illuminate\Support\Str::slug($g->name) === $categorySlug);

                $groupsToReturn = $matching->isNotEmpty() ? $matching : $filterGroups;

                $groupBag = [];
                foreach ($groupsToReturn as $group) {
                    if ($group->filters->isEmpty()) {
                        continue;
                    }
                    foreach ($group->filters as $filter) {
                        if ($filter->options->isEmpty()) {
                            continue; // skip filters with no options used in this category
                        }

                        // Keep same shape as original: value => label (swatch_value), no empty keys
                        $options = $filter->options->map(function ($option) {
                            if (is_null($option->swatch_value)) {
                                $option->swatch_value = \Illuminate\Support\Str::ucfirst($option->value);
                            }
                            return $option;
                        })->pluck('swatch_value', 'value')->toArray();

                        if (!empty($options)) {
                            $groupBag[$group->name][$filter->name] = $options;
                        }
                    }
                }

                return $groupBag; // may be a single group if matched by slug
            }

            return []; // unknown category URL -> empty
        }

        // Fallback (no category param): global list unchanged
        $filterGroups = \Mintreu\LaravelProductCatalogue\Models\FilterGroup::with([
            'filters.options'
        ])->get();

        $groupBag = [];
        foreach ($filterGroups as $group) {
            if ($group->filters->count()) {
                foreach ($group->filters as $filter) {
                    $options = $filter->options->map(function ($option) {
                        if (is_null($option->swatch_value)) {
                            $option->swatch_value = \Illuminate\Support\Str::ucfirst($option->value);
                        }
                        return $option;
                    });

                    $groupBag[$group->name][$filter->name] = $options->pluck('swatch_value', 'value')->toArray();
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

        return $allOptions->first(function ($sort) {
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

    public function show(Product $product)
    {
        $product->load([
            'media' => fn($query) => $query->where('collection_name','bannerImage'),
            'filterOptions.filter',
            'tiers',
            'categories',
            'sales' => fn($query) => $query
                ->with('sale')
                ->whereDate('starts_from', '<=', now()),
        ]);

        if (!is_null($product->parent_id)) {
            $product->load([
                'parent.variants.media',
                'parent.variants.filterOptions.filter',
            ]);
        } else {
            $product->load([
                'variants.media',
                'variants.filterOptions.filter',
            ]);
        }

        if ($product->category) {
            $siblings = $product->siblings()
                ->with(['media'])
                ->limit(4)
                ->get();

            $product->related_products = $siblings;
        }

        return new ProductResource($product);
    }

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
