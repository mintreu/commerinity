<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Blog\PostIndexResource;
use App\Http\Resources\Blog\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Mintreu\LaravelCategory\Models\Category;

class PostApiController extends Controller
{
    /**
     * Display a paginated listing of posts with filtering.
     */
    public function index(Request $request)
    {
        $query = Post::query()->with(['category', 'media', 'author']);

        // Filter by parent or child category slug (including children if parent given)
        if ($categorySlug = $request->query('category')) {
            $category = Category::where('url', $categorySlug)->first();

            if ($category) {
                $categoryIds = $category->descendantsAndSelf()->pluck('id')->toArray();
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Filter by author id
        if ($authorId = $request->query('author')) {
            $query->where('author_id', $authorId);
        }

        // Filter by date range
        if ($fromDate = $request->query('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate = $request->query('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Search filter (search in title/name and optionally content if needed)
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('content', 'like', '%'.$search.'%');
            });
        }

        $posts = $query->latest()->paginate(12);

        return PostIndexResource::collection($posts);
    }



    public function show(Post $post)
    {
        $post->load(['author', 'category', 'media']);
        return PostResource::make($post);
    }
}
