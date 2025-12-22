<?php

namespace App\Models\Traits\Cart;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasCartable
{


    /**
     * MorphOne relationship to Cart.
     */
    public function cart():MorphOne
    {
        return $this->morphOne(Cart::class, 'cartable');
    }

    public function carts():MorphMany
    {
        return $this->morphMany(Cart::class, 'cartable');
    }

}
