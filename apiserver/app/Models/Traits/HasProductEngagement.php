<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductEngagement;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * HasProductEngagement trait
 *
 * Add to User model to enable reviews/ratings functionality
 */
trait HasProductEngagement
{
    public function productEngagements(): MorphMany
    {
        return $this->morphMany(ProductEngagement::class, 'authorable');
    }

    public function reviews(): MorphMany
    {
        return $this->productEngagements()->topLevel();
    }

    /**
     * Review a product with optional rating
     */
    public function reviewProduct(int $productId, ?string $review = null, ?int $rating = null): ProductEngagement
    {
        return $this->productEngagements()->updateOrCreate(
            [
                'product_id' => $productId,
                'parent_id' => null,
            ],
            [
                'review' => $review,
                'rating' => $rating,
            ]
        );
    }

    /**
     * Rate a product (1-5 stars)
     */
    public function rateProduct(int $productId, int $rating): ProductEngagement
    {
        return $this->productEngagements()->updateOrCreate(
            [
                'product_id' => $productId,
                'parent_id' => null,
            ],
            [
                'rating' => min(5, max(1, $rating)),
            ]
        );
    }

    /**
     * Reply to a review/engagement
     */
    public function replyToEngagement(ProductEngagement $engagement, string $reply): ProductEngagement
    {
        return $this->productEngagements()->create([
            'product_id' => $engagement->product_id,
            'parent_id' => $engagement->id,
            'review' => $reply,
        ]);
    }

    /**
     * Check if user has reviewed a product
     */
    public function hasReviewedProduct(int $productId): bool
    {
        return $this->reviews()
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Get user's engagement for a product
     */
    public function getProductEngagement(int $productId): ?ProductEngagement
    {
        return $this->reviews()
            ->where('product_id', $productId)
            ->first();
    }
}
