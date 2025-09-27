<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\ProductEngagement; // Update this path when you move to package

trait HasProductEngagement
{
    /**
     * Get all product engagements for this authorable entity
     */
    public function productEngagements(): MorphMany
    {
        return $this->morphMany(ProductEngagement::class, 'authorable');
    }

    /**
     * Get only reviews (engagements with review content)
     */
    public function reviews(): MorphMany
    {
        return $this->productEngagements()->whereNotNull('review');
    }

    /**
     * Get only ratings (engagements with rating)
     */
    public function ratings(): MorphMany
    {
        return $this->productEngagements()->whereNotNull('rating');
    }

    /**
     * Get wishlisted products
     */
//    public function wishlistedProducts(): MorphMany
//    {
//        return $this->productEngagements()->where('wishlisted', true);
//    }

    /**
     * Create a review for a product
     */
    public function reviewProduct(int $productId, string $review, int $rating): ProductEngagement
    {
        return $this->productEngagements()->create([
            'product_id' => $productId,
            'review' => $review,
            'rating' => $rating,
        ]);
    }

    /**
     * Rate a product (without review)
     */
    public function rateProduct(int $productId, int $rating): ProductEngagement
    {
        return $this->productEngagements()->create([
            'product_id' => $productId,
            'rating' => $rating,
            'wishlisted' => false,
        ]);
    }

    /**
     * Add/Remove product from wishlist
     */
//    public function toggleWishlist(int $productId): ProductEngagement
//    {
//        $engagement = $this->productEngagements()
//            ->where('product_id', $productId)
//            ->first();
//
//        if ($engagement) {
//            $engagement->update(['wishlisted' => !$engagement->wishlisted]);
//            return $engagement;
//        }
//
//        return $this->productEngagements()->create([
//            'product_id' => $productId,
//            'wishlisted' => true,
//        ]);
//    }

    /**
     * Check if user has reviewed a specific product
     */
    public function hasReviewedProduct(int $productId): bool
    {
        return $this->productEngagements()
            ->where('product_id', $productId)
            ->whereNotNull('review')
            ->exists();
    }

    /**
     * Check if user has wishlisted a specific product
//     */
//    public function hasWishlistedProduct(int $productId): bool
//    {
//        return $this->productEngagements()
//            ->where('product_id', $productId)
//            ->where('wishlisted', true)
//            ->exists();
//    }

    /**
     * Get user's engagement with a specific product
     */
    public function getProductEngagement(int $productId): ?ProductEngagement
    {
        return $this->productEngagements()
            ->where('product_id', $productId)
            ->first();
    }
}
