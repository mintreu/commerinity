<?php

namespace App\Models\Traits;

use App\Models\ProductWishlist;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasProductWishlist
{
    public function productWishlist(): MorphMany
    {
        return $this->morphMany(ProductWishlist::class, 'authorable');
    }

    public function wishlistedProducts(): MorphMany
    {
        return $this->productWishlist();
    }

    public function addToWishlist(int $productId): ProductWishlist
    {
        return $this->productWishlist()->create([
            'product_id' => $productId,
        ]);
    }

    public function removeWishlist(int $productId): void
    {
        $this->productWishlist()->where('product_id', $productId)->delete();
    }
}

