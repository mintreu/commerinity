<?php

namespace App\Models\Lifecycle;

use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;

class UserSubscription extends Model
{
    protected $fillable = [
        'uuid',
        'amount',
        'is_paid',
        'expires_at',
        'checkout_expires_at',
        'user_id',
        'level_id',
        'stage_id',
        'wallet_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => LaravelMoneyCast::class,
        'is_paid' => 'boolean',
        'expires_at' => 'datetime',
        'checkout_expires_at' => 'datetime',
    ];
}
