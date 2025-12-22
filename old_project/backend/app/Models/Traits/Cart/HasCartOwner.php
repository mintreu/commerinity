<?php

namespace App\Models\Traits\Cart;


use App\Models\Cart;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCartOwner
{

    /**
     * MorphMany relationship to Cart.
     */
    public function cart():MorphMany
    {
        return $this->morphMany(Cart::class, 'ownerable');
    }

}
