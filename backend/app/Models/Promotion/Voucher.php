<?php

namespace App\Models\Promotion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    /** @use HasFactory<\Database\Factories\Promotion\VoucherFactory> */
    use HasFactory;


    protected $fillable = [
        'name',
        'description',
        'starts_from',
        'ends_till',
        'status',
        'usage_per_customer',
        'coupon_usage_limit',
        'times_used',
        'condition_type',
        'conditions',
        'end_other_rules',
        'action_type',
        'discount_amount',
        'discount_quantity',
        'discount_step',
        'apply_to_shipping',
        'free_shipping',
        'sort_order',
    ];

    protected $casts = [
        'starts_from' => 'date',
        'ends_till' => 'date',
        'status' => 'boolean',
        'condition_type' => 'boolean',
        'conditions' => 'array',
        'end_other_rules' => 'boolean',
        'discount_amount' => 'decimal:4',
        'discount_quantity' => 'integer',
        'discount_step' => 'string',
        'apply_to_shipping' => 'boolean',
        'free_shipping' => 'boolean',
        'sort_order' => 'integer',
        'usage_per_customer' => 'integer',
        'coupon_usage_limit' => 'integer',
        'times_used' => 'integer',
    ];

    // Helper Methods

    public function isActive(): bool
    {
        return $this->status && now()->between($this->starts_from, $this->ends_till);
    }

    public function isExpired(): bool
    {
        return $this->ends_till && now()->gt($this->ends_till);
    }

    public function canBeUsed(): bool
    {
        return $this->isActive() && $this->times_used < $this->coupon_usage_limit;
    }


}
