<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Content;

use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Content\ContentCategoryResource;
use App\Http\Resources\Content\ContentPostIndexResource;
use App\Http\Resources\Content\ContentPostResource;
use App\Models\Content\ContentCategory;
use App\Models\Content\ContentPost;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 9);
        $perPage = max(3, min($perPage, 24));

        $query = ContentPost::query()
            ->with('category')
            ->ofType(ContentType::News)
            ->published();

        if ($category = $request->query('category')) {
            $query->whereHas('category', function ($inner) use ($category) {
                $inner->where('slug', $category)->where('type', ContentType::News->value);
            });
        }

        if ($search = $request->query('search')) {
            $query->where(function ($inner) use ($search) {
                $inner->where('title', 'like', '%' . $search . '%')
                    ->orWhere('excerpt', 'like', '%' . $search . '%');
            });
        }

        $posts = $query->orderByDesc('published_at')->paginate($perPage)->withQueryString();

        return ContentPostIndexResource::collection($posts);
    }

    public function categories()
    {
        $categories = ContentCategory::query()
            ->active()
            ->ofType(ContentType::News)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ContentCategoryResource::collection($categories);
    }

    public function show(string $slug)
    {
        $post = ContentPost::query()
            ->with('category')
            ->ofType(ContentType::News)
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return new ContentPostResource($post);
    }

    public function related(string $slug)
    {
        $post = ContentPost::query()
            ->ofType(ContentType::News)
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedQuery = ContentPost::query()
            ->with('category')
            ->ofType(ContentType::News)
            ->published()
            ->where('id', '!=', $post->id);

        if ($post->category_id) {
            $relatedQuery->where('category_id', $post->category_id);
        }

        $related = $relatedQuery
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return ContentPostIndexResource::collection($related);
    }
}
