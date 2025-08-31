<?php

namespace App\Models\Order;

use App\Casts\OrderStatusCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;
use Mintreu\LaravelProductCatalogue\Models\Product;

class OrderProduct extends Model
{
    /** @use HasFactory<\Database\Factories\Order\OrderProductFactory> */
    use HasFactory;


    protected $fillable = [
        'quantity',
        'amount',
        'discount',
        'tax',
        'total',
        'has_tax',
        'product_id',
        'status',
        'status_feedback',
    ];

    protected $casts = [
        'amount' => LaravelMoneyCast::class,
        'subtotal' => LaravelMoneyCast::class,
        'discount' => LaravelMoneyCast::class,
        'tax' => LaravelMoneyCast::class,
        'total' => LaravelMoneyCast::class,
        'status' => OrderStatusCast::class
    ];



    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }


}
