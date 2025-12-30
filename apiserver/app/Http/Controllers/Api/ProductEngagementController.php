<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductEngagement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Product Engagement API Controller
 *
 * Handles product reviews, ratings, and helpful votes
 */
final class ProductEngagementController extends Controller
{
    /**
     * Get reviews for a product
     */
    public function index(Product $product): JsonResponse
    {
        $reviews = ProductEngagement::query()
            ->where('product_id', $product->id)
            ->topLevel()
            ->with(['authorable', 'replies.authorable'])
            ->mostHelpful()
            ->paginate(10);

        // Calculate average rating
        $stats = ProductEngagement::query()
            ->where('product_id', $product->id)
            ->topLevel()
            ->withRating()
            ->selectRaw('
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_reviews' => (int) $stats->total_reviews,
                    'average_rating' => round((float) ($stats->average_rating ?? 0), 1),
                    'distribution' => [
                        5 => (int) $stats->five_star,
                        4 => (int) $stats->four_star,
                        3 => (int) $stats->three_star,
                        2 => (int) $stats->two_star,
                        1 => (int) $stats->one_star,
                    ],
                ],
                'reviews' => $reviews->map(fn ($review) => $this->formatReview($review)),
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                    'has_more' => $reviews->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Create a review for a product
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $user = auth('sanctum')->user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required to submit a review',
            ], 401);
        }

        // Check if user already reviewed this product
        $existingReview = ProductEngagement::query()
            ->where('product_id', $product->id)
            ->where('authorable_id', $user->id)
            ->where('authorable_type', User::class)
            ->topLevel()
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product',
            ], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:2000',
        ]);

        $engagement = ProductEngagement::create([
            'product_id' => $product->id,
            'authorable_id' => $user->id,
            'authorable_type' => User::class,
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $this->formatReview($engagement->load('authorable')),
        ], 201);
    }

    /**
     * Update a review
     */
    public function update(Request $request, ProductEngagement $engagement): JsonResponse
    {
        $user = auth('sanctum')->user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        // Check ownership
        if ($engagement->authorable_id !== $user->id || $engagement->authorable_type !== User::class) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own reviews',
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'review' => 'nullable|string|max:2000',
        ]);

        $engagement->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $this->formatReview($engagement->fresh('authorable')),
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy(ProductEngagement $engagement): JsonResponse
    {
        $user = auth('sanctum')->user();

        if (! $user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        // Check ownership
        if ($engagement->authorable_id !== $user->id || $engagement->authorable_type !== User::class) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own reviews',
            ], 403);
        }

        $engagement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Mark a review as helpful
     */
    public function markHelpful(ProductEngagement $engagement): JsonResponse
    {
        $engagement->incrementHelpful();

        return response()->json([
            'success' => true,
            'message' => 'Marked as helpful',
            'data' => [
                'helpful_votes' => $engagement->fresh()->helpful_votes,
            ],
        ]);
    }

    /**
     * Format review for API response
     */
    private function formatReview(ProductEngagement $review): array
    {
        return [
            'id' => $review->id,
            'rating' => $review->rating,
            'review' => $review->review,
            'helpful_votes' => $review->helpful_votes,
            'created_at' => $review->created_at->toIso8601String(),
            'author' => [
                'name' => $review->authorable?->name ?? 'Anonymous',
                'avatar' => $review->authorable instanceof User
                    ? $review->authorable->getFirstMediaUrl('avatar')
                    : null,
            ],
            'replies' => $review->relationLoaded('replies')
                ? $review->replies->map(fn ($reply) => [
                    'id' => $reply->id,
                    'review' => $reply->review,
                    'created_at' => $reply->created_at->toIso8601String(),
                    'author' => [
                        'name' => $reply->authorable?->name ?? 'Anonymous',
                    ],
                ])->toArray()
                : [],
        ];
    }
}
