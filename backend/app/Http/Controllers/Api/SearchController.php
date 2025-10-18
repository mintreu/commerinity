<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Blog\PostIndexResource;
use App\Http\Resources\Catalogue\Product\ProductIndexResource;
use App\Http\Resources\Category\CategoryIndexResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelCategory\Models\Category;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|min:2|max:255',
            'category' => 'nullable|string',
            'filter' => 'nullable|string|in:deals,blog',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:50',
            'sort' => 'nullable|string|in:relevance,latest,pricelow2high,pricehigh2low'
        ]);

        $query = $validated['search'];
        $page = $validated['page'] ?? 1;
        $limit = $validated['limit'] ?? 12;
        $sort = $validated['sort'] ?? 'relevance';
        $category = $validated['category'] ?? null;
        $filter = $validated['filter'] ?? null;

        $cacheKey = "global_search:{$query}:{$page}:{$limit}:{$sort}:{$category}:{$filter}";

        $results = Cache::remember($cacheKey, 300, function () use ($query, $page, $limit, $sort, $category, $filter) {
            return $this->performGlobalSearch($query, $page, $limit, $sort, $category, $filter);
        });

        return response()->json([
            'success' => true,
            'data' => $results['data'],
            'total' => $results['total'],
            'meta' => [
                'current_page' => $page,
                'per_page' => $limit,
                'last_page' => $results['last_page'],
                'total' => $results['total']
            ],
            'query' => $query
        ]);
    }

    protected function performGlobalSearch($query, $page, $limit, $sort, $category = null, $filter = null)
    {
        $allResults = collect();

        // Filter-based search
        if ($filter === 'blog') {
            $posts = $this->searchPosts($query, $sort);
            $allResults = $allResults->concat($posts);
        } elseif ($filter === 'deals') {
            $products = $this->searchProducts($query, $sort, $category, true);
            $allResults = $allResults->concat($products);
        } else {
            // Global search
            $products = $this->searchProducts($query, $sort, $category);
            $allResults = $allResults->concat($products);

            $categories = $this->searchCategories($query);
            $allResults = $allResults->concat($categories);

            $posts = $this->searchPosts($query, $sort);
            $allResults = $allResults->concat($posts);
        }

        $total = $allResults->count();
        $offset = ($page - 1) * $limit;
        $paginatedResults = $allResults->slice($offset, $limit)->values();

        return [
            'data' => $paginatedResults,
            'total' => $total,
            'last_page' => ceil($total / $limit)
        ];
    }

    protected function searchProducts($query, $sort, $category = null, $dealsOnly = false)
    {
        $productsQuery = Product::query()
            ->where('status', 'published')
            ->where('type', 'configurable')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('sku', 'LIKE', "%{$query}%")
                    ->orWhere('url', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhere('short_description', 'LIKE', "%{$query}%");
            })
            ->with([
                'media' => fn($q) => $q->where('collection_name', 'displayImage')->limit(1),
                'categories' => fn($q) => $q->limit(1)
            ]);

        // Filter by category
        if ($category) {
            $productsQuery->whereHas('categories', function($q) use ($category) {
                $q->where('url', $category);
            });
        }

        // Filter by deals (products with sales)
        if ($dealsOnly) {
            $productsQuery->whereHas('sales');
        }

        // Apply sorting
        switch ($sort) {
            case 'latest':
                $productsQuery->orderBy('created_at', 'desc');
                break;
            case 'pricelow2high':
                $productsQuery->orderBy('price', 'asc');
                break;
            case 'pricehigh2low':
                $productsQuery->orderBy('price', 'desc');
                break;
            case 'relevance':
            default:
                $productsQuery->orderByRaw("
                    CASE
                        WHEN name = ? THEN 1
                        WHEN name LIKE ? THEN 2
                        WHEN sku = ? THEN 3
                        ELSE 4
                    END
                ", [$query, "{$query}%", $query]);
                break;
        }

        $products = $productsQuery->limit(50)->get();

        return ProductIndexResource::collection($products)->map(function ($resource) {
            $data = $resource->resolve();
            $data['type'] = 'product';
            $data['display_image'] = $data['thumbnail'] ?? null;
            return $data;
        });
    }

    protected function searchCategories($query)
    {
        $categories = Category::query()
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('url', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        return CategoryIndexResource::collection($categories)->map(function ($resource) {
            $data = $resource->resolve();
            $data['type'] = 'category';
            $data['display_image'] = $data['thumbnail'] ?? null;
            $data['description'] = 'Browse this category';
            return $data;
        });
    }

    protected function searchPosts($query, $sort)
    {
        $postsQuery = Post::query()
            ->where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('url', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->with([
                'media' => fn($q) => $q->where('collection_name', 'displayImage')->limit(1),
                'category',
                'author'
            ]);

        if ($sort === 'latest') {
            $postsQuery->orderBy('created_at', 'desc');
        } else {
            $postsQuery->orderByRaw("
                CASE
                    WHEN name = ? THEN 1
                    WHEN name LIKE ? THEN 2
                    ELSE 3
                END
            ", [$query, "{$query}%"]);
        }

        $posts = $postsQuery->limit(20)->get();

        return PostIndexResource::collection($posts)->map(function ($resource) use ($posts) {
            $data = $resource->resolve();
            $post = $posts->firstWhere('url', $data['url']);

            $data['type'] = 'post';
            $data['display_image'] = $data['thumbnail'] ?? null;
            $data['title'] = $data['name'];
            $data['slug'] = $data['url'];

            if ($post && $post->description) {
                $data['description'] = $post->description;
                $data['excerpt'] = strlen($post->description) > 150
                    ? substr($post->description, 0, 150) . '...'
                    : $post->description;
            }

            return $data;
        });
    }
}
