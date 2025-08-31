<?php

namespace App\Models\Order;

use App\Casts\OrderStatusCast;
use Faker\Provider\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Mintreu\LaravelGeokit\Traits\HasAddress;
use Mintreu\LaravelGeokit\Traits\HasOrderAddresses;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;
use Mintreu\LaravelTransaction\Traits\HasTransaction;
use Mintreu\Toolkit\Traits\HasUnique;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\Order\OrderFactory> */
    use HasFactory,HasOrderAddresses,HasTransaction,HasUnique;

    protected string $billingAddressKey = 'billing_address_id';
    protected string $shippingAddressKey = 'shipping_address_id';


    protected $fillable = [
        'uuid',
        'amount',
        'subtotal',
        'discount',
        'tax',
        'total',
        'quantity',
        'voucher',
        'is_cod',
        'tracking_id',
        'status',
        'payment_success',
        'expire_at',
        'customerable_type',
        'customerable_id',
        'shipping_is_billing',
        'billing_address_id',
        'shipping_address_id',
    ];

    protected $casts = [
        'amount' => LaravelMoneyCast::class,
        'subtotal' => LaravelMoneyCast::class,
        'discount' => LaravelMoneyCast::class,
        'tax' => LaravelMoneyCast::class,
        'total' => LaravelMoneyCast::class,
        'status' => OrderStatusCast::class,
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->setUniqueCode('uuid', 12, now()->year);
        });
        parent::booted();
    }

    public function customerable()
    {
        return $this->morphTo();
    }


    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class,'order_id','id');
    }





}
