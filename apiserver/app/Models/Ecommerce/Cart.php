<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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


    public function cartable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ownerable(): MorphTo
    {
        return $this->morphTo();
    }

}
