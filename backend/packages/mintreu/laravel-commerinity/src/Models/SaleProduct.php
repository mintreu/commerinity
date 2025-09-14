<?php

namespace Mintreu\LaravelCommerinity\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mintreu\LaravelCommerinity\Casts\SaleActionTypeCast;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\Toolkit\Traits\HasPackageModelFactory;

class SaleProduct extends Model
{
    /** @use HasFactory<\Mintreu\LaravelCommerinity\Database\Factories\SaleProductFactory> */
    use HasPackageModelFactory;

    protected $fillable = [
        'starts_from',
        'ends_till',
        'end_other_rules',
        'action_type',
        'sale_price',
        'discount_amount',
        'sort_order',
        'sale_id',
        'product_id',
        'target_type',
        'target_id',
    ];

    protected $casts = [
        'sale_price'      => LaravelMoneyCast::class,
        'discount_amount' => LaravelMoneyCast::class,
        'starts_from'     => 'datetime',
        'ends_till'       => 'datetime',
        'action_type'     => SaleActionTypeCast::class
    ];

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subMonth());
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
