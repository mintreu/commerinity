<?php

namespace App\Models\Promotion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelProductCatalogue\Models\Product;

class SaleProduct extends Model
{
    /** @use HasFactory<\Database\Factories\Promotion\SaleProductFactory> */
    use HasFactory;


    protected $fillable = [

        'starts_from',
        'ends_till',
        'end_other_rules',
        'action_type',
        'sale_price',
        'discount_amount',
        'sort_order',
    ];

    protected $casts = [
        'sale_price' => MoneyCast::class,
        'discount_amount' => MoneyCast::class,
    ];

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subMonth());
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }



}
