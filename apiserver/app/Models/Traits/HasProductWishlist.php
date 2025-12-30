<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductWishlist;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * HasProductWishlist trait
 *
 * Add to User model to enable wishlist/favorites functionality
 */
trait HasProductWishlist
{
    public function productWishlist(): MorphMany
    {
        return $this->morphMany(ProductWishlist::class, 'authorable');
    }

    /**
     * Alias for productWishlist
     */
    public function wishlistedProducts(): MorphMany
    {
        return $this->productWishlist();
    }

    /**
     * Add a product to wishlist
     */
    public function addToWishlist(int $productId): ProductWishlist
    {
        return $this->productWishlist()->firstOrCreate([
            'product_id' => $productId,
        ]);
    }

    /**
     * Remove a product from wishlist
     */
    public function removeFromWishlist(int $productId): bool
    {
        return $this->productWishlist()
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    /**
     * Toggle wishlist status for a product
     * Returns true if added, false if removed
     */
    public function toggleWishlist(int $productId): bool
    {
        if ($this->hasInWishlist($productId)) {
            $this->removeFromWishlist($productId);

            return false;
        }

        $this->addToWishlist($productId);

        return true;
    }

    /**
     * Check if product is in wishlist
     */
    public function hasInWishlist(int $productId): bool
    {
        return $this->productWishlist()
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Get wishlist product IDs
     */
    public function wishlistProductIds(): array
    {
        return $this->productWishlist()
            ->pluck('product_id')
            ->toArray();
    }
}
