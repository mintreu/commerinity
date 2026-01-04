<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Helpdesk\HelpdeskFaq;
use App\Models\Helpdesk\HelpdeskTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class FaqController extends Controller
{
    /**
     * Get all FAQ topics with FAQ counts.
     *
     * GET /api/faq/topics
     */
    public function topics(): JsonResponse
    {
        $topics = HelpdeskTopic::query()
            ->active()
            ->ordered()
            ->withCount(['faqs' => fn ($q) => $q->active()])
            ->get()
            ->map(fn (HelpdeskTopic $topic) => [
                'id' => $topic->id,
                'name' => $topic->name,
                'slug' => $topic->slug,
                'description' => $topic->description,
                'icon' => $topic->icon,
                'tickable' => $topic->tickable,
                'faqs_count' => $topic->faqs_count,
            ]);

        return response()->json([
            'success' => true,
            'data' => ['topics' => $topics],
        ]);
    }

    /**
     * Get FAQs by topic slug.
     *
     * GET /api/faq/{topicSlug}
     */
    public function byTopic(string $topicSlug, Request $request): JsonResponse
    {
        $topic = HelpdeskTopic::query()
            ->where('slug', $topicSlug)
            ->active()
            ->first();

        if (! $topic) {
            return response()->json([
                'success' => false,
                'message' => 'Topic not found',
            ], 404);
        }

        $faqs = HelpdeskFaq::query()
            ->byTopic($topic->id)
            ->active()
            ->ordered()
            ->get()
            ->map(fn (HelpdeskFaq $faq) => [
                'id' => $faq->id,
                'url' => $faq->url,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'tags' => $faq->tags,
                'keywords' => $faq->keywords,
                'views' => $faq->views,
                'helpful_count' => $faq->helpful_count,
                'not_helpful_count' => $faq->not_helpful_count,
                'helpful_percentage' => $faq->helpful_percentage,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'topic' => [
                    'name' => $topic->name,
                    'slug' => $topic->slug,
                    'description' => $topic->description,
                    'icon' => $topic->icon,
                ],
                'faqs' => $faqs,
            ],
        ]);
    }

    /**
     * Get a single FAQ by URL slug.
     *
     * GET /api/faq/view/{url}
     */
    public function show(string $url): JsonResponse
    {
        $faq = HelpdeskFaq::query()
            ->where('url', $url)
            ->active()
            ->with('topic')
            ->first();

        if (! $faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found',
            ], 404);
        }

        // Increment view count
        $faq->incrementViews();

        return response()->json([
            'success' => true,
            'data' => [
                'faq' => [
                    'id' => $faq->id,
                    'url' => $faq->url,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'tags' => $faq->tags,
                    'keywords' => $faq->keywords,
                    'views' => $faq->views,
                    'helpful_count' => $faq->helpful_count,
                    'not_helpful_count' => $faq->not_helpful_count,
                    'helpful_percentage' => $faq->helpful_percentage,
                    'topic' => [
                        'name' => $faq->topic->name,
                        'slug' => $faq->topic->slug,
                        'icon' => $faq->topic->icon,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Search FAQs.
     *
     * GET /api/faq/search?q=keyword
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q');

        if (! $query) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required',
            ], 400);
        }

        $faqs = HelpdeskFaq::query()
            ->active()
            ->search($query)
            ->with('topic')
            ->limit(20)
            ->get()
            ->map(fn (HelpdeskFaq $faq) => [
                'id' => $faq->id,
                'url' => $faq->url,
                'question' => $faq->question,
                'answer' => substr($faq->answer, 0, 200).'...', // Preview only
                'topic' => [
                    'name' => $faq->topic->name,
                    'slug' => $faq->topic->slug,
                    'icon' => $faq->topic->icon,
                ],
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'query' => $query,
                'results' => $faqs,
                'count' => $faqs->count(),
            ],
        ]);
    }

    /**
     * Mark FAQ as helpful.
     *
     * POST /api/faq/{url}/helpful
     */
    public function markHelpful(string $url): JsonResponse
    {
        $faq = HelpdeskFaq::query()
            ->where('url', $url)
            ->active()
            ->first();

        if (! $faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found',
            ], 404);
        }

        $faq->markHelpful();

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
        ]);
    }

    /**
     * Mark FAQ as not helpful.
     *
     * POST /api/faq/{url}/not-helpful
     */
    public function markNotHelpful(string $url): JsonResponse
    {
        $faq = HelpdeskFaq::query()
            ->where('url', $url)
            ->active()
            ->first();

        if (! $faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found',
            ], 404);
        }

        $faq->markNotHelpful();

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback! We\'ll work on improving this answer.',
        ]);
    }

    /**
     * Get popular FAQs.
     *
     * GET /api/faq/popular
     */
    public function popular(): JsonResponse
    {
        $faqs = HelpdeskFaq::query()
            ->active()
            ->popular()
            ->with('topic')
            ->limit(10)
            ->get()
            ->map(fn (HelpdeskFaq $faq) => [
                'id' => $faq->id,
                'url' => $faq->url,
                'question' => $faq->question,
                'views' => $faq->views,
                'topic' => [
                    'name' => $faq->topic->name,
                    'slug' => $faq->topic->slug,
                    'icon' => $faq->topic->icon,
                ],
            ]);

        return response()->json([
            'success' => true,
            'data' => ['faqs' => $faqs],
        ]);
    }
}
