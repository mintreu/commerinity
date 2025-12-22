<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'quantity',
        'discount',
        'cartable_id',
        'cartable_type',
        'ownerable_id',
        'ownerable_type',
        'guest_id',
        'guest_token',
        'is_guest'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'discount' => 'integer',
        'is_guest' => 'boolean'
    ];


    /**
     * Polymorphic relationship for the cart item (Product or Service).
     */
    public function cartable()
    {
        return $this->morphTo();
    }

    /**
     * Polymorphic relationship for the cart owner (User or Customer).
     */
    public function ownerable()
    {
        return $this->morphTo();
    }

}
