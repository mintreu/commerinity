<?php

namespace App\Models\Promotion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;

class Sale extends Model
{
    /** @use HasFactory<\Database\Factories\Promotion\SaleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'starts_from',
        'ends_till',
        'status',
        'condition_type',
        'conditions',
        'end_other_rules',
        'action_type',
        'discount_amount',
        'sort_order',
    ];

    protected $casts = [
        'conditions' => 'array',
        'discount_amount' => LaravelMoneyCast::class,
    ];





    /**
     * Get the Sale Products in the sale.
     */
    public function sale_products(): HasMany
    {
        return $this->hasMany(SaleProduct::class);
    }








}
