<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductEngagementResource;
use App\Models\ProductEngagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Mintreu\LaravelProductCatalogue\Models\Product;

class ProductEngagementController extends Controller
{
    /**
     * Get all reviews for a product
     */
    public function index(Product $product, Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $engagements = ProductEngagement::where('product_id', $product->id)
            ->whereNotNull('review') // Only get reviews (not just ratings or wishlists)
            ->with(['authorable']) // Load the polymorphic author relationship
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        return ProductEngagementResource::collection($engagements);
    }

    /**
     * Store a new review for a product
     */
    public function store(Product $product, Request $request): ProductEngagementResource|JsonResponse
    {
        $validated = $request->validate([
            'review' => 'required|string|min:10|max:500',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        $author = $request->user();
        $existingReview = $author->productEngagements()
            ->where('product_id', $product->id)
            ->whereNotNull('review') // Check for actual reviews, not just any engagement
            ->count();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product. You can edit your existing review.'
            ], 422);
        }

        $newEngagement = $author->productEngagements()->create([
            'product_id' => $product->id,
            'review' => $validated['review'],
            'rating' => $validated['rating'],
//            'wishlisted' => false,
        ]);

        return ProductEngagementResource::make($newEngagement->load('authorable'));
    }

    /**
     * Update an existing review
     */
    public function update(ProductEngagement $engagement, Request $request): ProductEngagementResource|JsonResponse
    {
        $author = $request->user();
        $engagement->load('authorable');
        // Check if the current user owns this engagement
        if ($engagement->authorable_id !== $author->id || $engagement->authorable_type !== get_class($author)) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update your own reviews'
            ], 403);
        }

        $validated = $request->validate([
            'review' => 'required|string|min:10|max:500',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        $engagement->update([
            'review' => $validated['review'],
            'rating' => $validated['rating'],
        ]);

        return ProductEngagementResource::make($engagement);
    }

    /**
     * Delete a review
     */
    public function destroy(ProductEngagement $engagement, Request $request): JsonResponse
    {
        try {
            $author = $request->user();

            if ($engagement->authorable_id !== $author->id || $engagement->authorable_type !== get_class($author)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own reviews'
                ], 403);
            }

            $engagement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Mark engagement as helpful
     */
    public function helpFullEngagement(ProductEngagement $engagement, Request $request): JsonResponse
    {
        try {
            $author = $request->user();

            // Prevent users from marking their own reviews as helpful
            if ($engagement->authorable_id === $author->id && $engagement->authorable_type === get_class($author)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot mark your own review as helpful'
                ], 422);
            }

            // For now, just increment - you might want to implement proper helpful tracking later
            $engagement->increment('helpful_votes');

            return response()->json([
                'success' => true,
                'message' => 'Review marked as helpful!',
                'helpful_votes' => $engagement->helpful_votes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as helpful',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Calculate average rating for a product
     */
    private function calculateAverageRating(Product $product): float
    {
        $average = ProductEngagement::where('product_id', $product->id)
            ->whereNotNull('rating')
            ->avg('rating');

        return round($average ?? 0, 2);
    }
}
