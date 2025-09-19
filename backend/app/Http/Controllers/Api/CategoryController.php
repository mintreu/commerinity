<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Category\CategoryIndexResource;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Product\ProductIndexResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::tree()
            ->where('status', true)
            ->where('is_visible_on_front', true)
            ->with([
                'media' => fn($query) => $query->where('collection_name', 'displayImage')
            ])
            ->limit(12)
            ->get()
            ->toTree();

        return CategoryIndexResource::collection($categories);
    }

    public function getParentCategoriesWithProducts(): JsonResponse
    {
        $parentCategories = Category::with([
            'media' => fn($q) => $q->where('collection_name', 'displayImage'),
            'children' => fn($q) => $q
                ->where('status', true)
                ->where('is_visible_on_front', true)
                ->with(['media' => fn($q) => $q->where('collection_name', 'displayImage'), 'descendants']),
        ])
            ->whereNull('parent_id')
            ->where('status', true)
            ->where('is_visible_on_front', true)
            ->get();

        $childToAllCategoryIds = [];
        foreach ($parentCategories as $parent) {
            foreach ($parent->children as $child) {
                $descendantIds = $child->descendants->pluck('id');
                $childToAllCategoryIds[$child->id] = $descendantIds->push($child->id)->unique()->toArray();
            }
        }

        $allRelevantCategoryIds = collect($childToAllCategoryIds)->flatten()->unique()->toArray();

        $lowestPrices = DB::table('products')
            ->join('category_mappings', function ($join) {
                $join->on('products.id', '=', 'category_mappings.categorized_id')
                    ->where('category_mappings.categorized_type', Product::class);
            })
            ->join('product_tiers', 'products.id', '=', 'product_tiers.product_id')
            ->where('products.status', PublishableStatusCast::PUBLISHED->value)
            ->whereIn('category_mappings.category_id', $allRelevantCategoryIds)
            ->where('product_tiers.in_stock', true)
            ->selectRaw('MIN(product_tiers.price) as min_price, category_mappings.category_id')
            ->groupBy('category_mappings.category_id')
            ->pluck('min_price', 'category_mappings.category_id');

        $response = $parentCategories
            ->map(function ($parent) use ($childToAllCategoryIds, $lowestPrices) {
                $childrenData = [];

                foreach ($parent->children as $child) {
                    $ids = $childToAllCategoryIds[$child->id] ?? [];
                    $lowest = collect($ids)->map(fn($i) => $lowestPrices[$i] ?? null)->filter()->min();

                    if ($lowest !== null) {
                        $childrenData[] = [
                            'name' => $child->name,
                            'url' => $child->url,
                            'image' => $child->getFirstMediaUrl('displayImage'),
                            'starting_from_price' => $lowest,
                        ];
                    }
                }

                return count($childrenData) > 0
                    ? [
                        'url' => $parent->url,
                        'name' => $parent->name,
                        'thumbnail' => $parent->getFirstMediaUrl('displayImage'),
                        'children' => $childrenData,
                    ]
                    : null;
            })
            ->filter()
            ->values();

        return response()->json($response);
    }

    // UPDATED: apply filters/price/offer/in_stock/sort to "All Products"
    public function show(Request $request, Category $category): CategoryResource
    {
        $category->load([
            'descendants',
            'children',
            'children.media',
        ]);

        // All related category IDs (self + descendants)
        $categoryIds = $category->descendants->pluck('id')->push($category->id);

        // Base query (only published parents in tree)
        $query = Product::query()
            ->whereNull('parent_id')
            ->where('status', PublishableStatusCast::PUBLISHED->value)
            ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds))
            ->with(['media' => fn($q) => $q->where('collection_name', 'displayImage')]);

        // filters[FilterName][]=OptionValue (AND across filters, OR within values)
        $filters = $request->input('filters', []);
        if (is_array($filters)) {
            foreach ($filters as $filterName => $values) {
                $values = array_values(array_filter((array) $values, fn($v) => $v !== null && $v !== ''));
                if (count($values) > 0) {
                    $query->whereHas('filterOptions', function ($fo) use ($filterName, $values) {
                        $fo->whereHas('filter', fn($f) => $f->where('name', $filterName))
                            ->whereIn('value', $values);
                    });
                }
            }
        }

        // price[min], price[max] on products.price (adjust if tier-based pricing is required)
        $price = $request->input('price', []);
        if (is_array($price)) {
            if (isset($price['min']) && $price['min'] !== '') {
                $query->where('price', '>=', (float)$price['min']);
            }
            if (isset($price['max']) && $price['max'] !== '') {
                $query->where('price', '<=', (float)$price['max']);
            }
        }

        // offer=1 => products that currently have sales started
        if ($request->boolean('offer')) {
            $query->whereHas('sales', fn($s) => $s->whereDate('starts_from', '<=', now()));
        }

        // in_stock=1 => products that have at least one in-stock tier
        if ($request->boolean('in_stock')) {
            $query->whereHas('tiers', fn($t) => $t->where('in_stock', true));
        }

        // Sorting
        $sort = $request->input('sort', []);
        $column = 'created_at';
        $direction = 'desc';
        if (is_array($sort) && !empty($sort)) {
            $key = array_key_first($sort);
            $dir = strtolower((string)($sort[$key] ?? 'desc'));
            $direction = in_array($dir, ['asc', 'desc'], true) ? $dir : 'desc';

            switch ($key) {
                case 'popularity':
                    $column = 'view_count';
                    break;
                case 'latest':
                    $column = 'created_at';
                    $direction = 'desc';
                    break;
                case 'pricelow2high':
                    $column = 'price';
                    $direction = 'asc';
                    break;
                case 'pricehigh2low':
                    $column = 'price';
                    $direction = 'desc';
                    break;
                default:
                    $column = 'created_at';
                    $direction = 'desc';
            }
        }

        // Top viewed (unchanged / not filtered)
        $topProducts = Product::query()
            ->whereNull('parent_id')
            ->where('status', PublishableStatusCast::PUBLISHED->value)
            ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds))
            ->with(['media' => fn($q) => $q->where('collection_name', 'displayImage')])
            ->orderBy('view_count', 'desc')
            ->take(20)
            ->get();

        // Latest (unchanged / not filtered)
        $latestProducts = Product::query()
            ->whereNull('parent_id')
            ->where('status', PublishableStatusCast::PUBLISHED->value)
            ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds))
            ->with(['media' => fn($q) => $q->where('collection_name', 'displayImage')])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        // All products with filters and sorting
        $allProducts = (clone $query)
            ->orderBy($column, $direction)
            ->paginate(15);

        return (new CategoryResource($category))->additional([
            'top_products'    => ProductIndexResource::collection($topProducts),
            'latest_products' => ProductIndexResource::collection($latestProducts),
            'all_products'    => ProductIndexResource::collection($allProducts),
            'pagination'      => [
                'current_page' => $allProducts->currentPage(),
                'last_page'    => $allProducts->lastPage(),
                'total'        => $allProducts->total(),
                'per_page'     => $allProducts->perPage(),
            ],
        ]);
    }
}
