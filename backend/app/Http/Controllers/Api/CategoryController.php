<?php

namespace App\Http\Controllers\Api;

use App\Casts\ModelStatusCast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Category\CategoryIndexResource;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Product\ProductIndexResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{

    public function index()
    {
        $categories = Category::tree()
            ->where('status',true)
            ->where('is_visible_on_front',true)
            ->with([
                'media' => fn($query) => $query->where('collection_name','displayImage')
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

//        $lowestPrices = Product::selectRaw('MIN(price) as min_price, category_mappings.category_id')
//            ->join('category_mappings', fn($join) => $join
//                ->on('products.id', '=', 'category_mappings.categorized_id')
//                ->where('category_mappings.categorized_type', Product::class)
//            )
//            ->whereNull('products.parent_id')
//            ->whereIn('category_mappings.category_id', $allRelevantCategoryIds)
//            ->groupBy('category_mappings.category_id')
//            ->pluck('min_price', 'category_mappings.category_id');



        $lowestPrices =  DB::table('products')
            ->join('category_mappings', function ($join) {
                $join->on('products.id', '=', 'category_mappings.categorized_id')
                    ->where('category_mappings.categorized_type', Product::class);
            })
            ->join('product_tiers', 'products.id', '=', 'product_tiers.product_id')
            ->where('products.status', ModelStatusCast::PUBLISHED->value)
            ->where('products.parent_id', null)
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
            ->filter() // Remove parents without any valid children
            ->values(); // Re-index array

        return response()->json($response);
    }








    public function show(Category $category): CategoryResource
    {
        $category->load([
            'descendants',
            'children',
            'children.media',
        ]);

        // Get category IDs including descendants + self
        $categoryIds = $category->descendants->pluck('id')->push($category->id);

        // Get top 4 viewed products mapped to these categories
        $topProducts = Product::whereNull('parent_id')
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->with([
                'media' => fn($query) => $query->where('collection_name','displayImage')
            ])
            ->orderBy('view_count', 'desc')
            ->take(12)
            ->get();

        // Get latest 4 products mapped to these categories
        $latestProducts = Product::whereNull('parent_id')
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->with([
                'media' => fn($query) => $query->where('collection_name','displayImage')
            ])
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        return (new CategoryResource($category))
            ->additional([
                'top_products' => ProductIndexResource::collection($topProducts),
                'latest_products' => ProductIndexResource::collection($latestProducts),
            ]);
    }



}
