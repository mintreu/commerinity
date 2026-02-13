<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Models\Content\ContentPost;
use App\Services\Ecommerce\ProductQueryService;
use App\Services\MoneyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

final class GlobalSearchController extends Controller
{
    public function __construct(
        private readonly ProductQueryService $productQueryService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $perType = max(1, min((int) $request->query('per_type', 6), 20));
        $tokens = $this->tokenizeSearch($query);

        if (mb_strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'results' => [
                        'products' => [],
                        'blogs' => [],
                        'news' => [],
                    ],
                    'totals' => [
                        'products' => 0,
                        'blogs' => 0,
                        'news' => 0,
                        'all' => 0,
                    ],
                ],
            ]);
        }

        $productsBaseQuery = $this->productQueryService->storefrontBaseQuery();
        $this->applyLikeSearch(
            $productsBaseQuery,
            $tokens,
            ['name', 'sku', 'short_description', 'description', 'url']
        );
        $productsTotal = (clone $productsBaseQuery)->count();

        $productsQuery = (clone $productsBaseQuery)->limit($perType);
        $this->productQueryService->applyStorefrontEagerLoads($productsQuery);

        $products = $productsQuery
            ->get()
            ->map(function ($product) {
                $image = $product->getFirstMedia('displayImage');

                return [
                    'id' => $product->id,
                    'type' => 'product',
                    'title' => $product->name,
                    'slug' => $product->url,
                    'sku' => $product->sku,
                    'excerpt' => $product->short_description,
                    'price_formatted' => MoneyService::format($product->getPrice()),
                    'thumbnail' => $image?->hasGeneratedConversion('thumb') ? $image?->getUrl('thumb') : $image?->getUrl(),
                    'url' => '/shop/product/'.$product->url,
                ];
            })
            ->values();

        $blogsTotal = 0;
        $blogs = collect();
        $newsTotal = 0;
        $news = collect();

        if (Schema::hasTable('content_posts')) {
            $searchableColumns = $this->availableContentSearchColumns();

            $blogsBaseQuery = ContentPost::query()
                ->ofType(ContentType::Blog)
                ->published();
            $this->applyLikeSearch($blogsBaseQuery, $tokens, $searchableColumns);
            $blogsTotal = (clone $blogsBaseQuery)->count();
            $blogs = $blogsBaseQuery
                ->orderByDesc('published_at')
                ->limit($perType)
                ->get()
                ->map(fn (ContentPost $post) => [
                    'id' => $post->id,
                    'type' => 'blog',
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'excerpt' => $post->excerpt,
                    'thumbnail' => $post->cover_image,
                    'published_at' => $post->published_at?->toIso8601String(),
                    'url' => '/blogs/'.$post->slug,
                ])
                ->values();

            $newsBaseQuery = ContentPost::query()
                ->ofType(ContentType::News)
                ->published();
            $this->applyLikeSearch($newsBaseQuery, $tokens, $searchableColumns);
            $newsTotal = (clone $newsBaseQuery)->count();
            $news = $newsBaseQuery
                ->orderByDesc('published_at')
                ->limit($perType)
                ->get()
                ->map(fn (ContentPost $post) => [
                    'id' => $post->id,
                    'type' => 'news',
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'excerpt' => $post->excerpt,
                    'thumbnail' => $post->cover_image,
                    'published_at' => $post->published_at?->toIso8601String(),
                    'url' => '/news/'.$post->slug,
                ])
                ->values();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'query' => $query,
                'results' => [
                    'products' => $products,
                    'blogs' => $blogs,
                    'news' => $news,
                ],
                'totals' => [
                    'products' => $productsTotal,
                    'blogs' => $blogsTotal,
                    'news' => $newsTotal,
                    'all' => $productsTotal + $blogsTotal + $newsTotal,
                ],
            ],
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function tokenizeSearch(string $query): array
    {
        return array_values(array_filter(
            preg_split('/\s+/u', trim($query)) ?: [],
            static fn (string $token) => $token !== ''
        ));
    }

    /**
     * Apply tokenized LIKE matching:
     * - each token must match at least one searchable column
     * - `%` and `_` are escaped so user input remains literal
     *
     * @param array<int, string> $tokens
     * @param array<int, string> $columns
     */
    private function applyLikeSearch($query, array $tokens, array $columns): void
    {
        foreach ($tokens as $token) {
            $escapedToken = addcslashes($token, '\\%_');
            $query->where(function ($inner) use ($columns, $escapedToken) {
                foreach ($columns as $index => $column) {
                    if ($index === 0) {
                        $inner->where($column, 'like', "%{$escapedToken}%");
                    } else {
                        $inner->orWhere($column, 'like', "%{$escapedToken}%");
                    }
                }
            });
        }
    }

    /**
     * @return array<int, string>
     */
    private function availableContentSearchColumns(): array
    {
        $columns = ['title', 'excerpt'];
        if (Schema::hasColumn('content_posts', 'content')) {
            $columns[] = 'content';
        }

        return $columns;
    }
}
