<?php

namespace Mintreu\LaravelCommerinity\Traits;


use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Mintreu\LaravelCommerinity\Models\Voucher;

trait HasVoucherAccess
{
    /**
     * Get all vouchers linked to this model.
     */
    public function vouchers(): MorphToMany
    {
        return $this->morphToMany(
            Voucher::class,
            'target',
            'voucher_targets'
        );
    }

    /**
     * Scope: filter vouchers that are currently active.
     */
    public function scopeWithActiveVouchers(Builder $query): Builder
    {
        return $query->whereHas('vouchers', function ($q) {
            $q->where('status', true)
                ->where(function ($q2) {
                    $now = now();
                    $q2->whereNull('starts_from')->orWhere('starts_from', '<=', $now);
                })
                ->where(function ($q2) {
                    $now = now();
                    $q2->whereNull('ends_till')->orWhere('ends_till', '>=', $now);
                });
        });
    }

    /**
     * Scope: filter vouchers by action type.
     */
    public function scopeWithVouchersOfType(Builder $query, string $actionType): Builder
    {
        return $query->whereHas('vouchers', function ($q) use ($actionType) {
            $q->where('action_type', $actionType);
        });
    }

    /**
     * Scope: filter vouchers by minimum discount amount.
     */
    public function scopeWithVouchersMinDiscount(Builder $query, int $amount): Builder
    {
        return $query->whereHas('vouchers', function ($q) use ($amount) {
            $q->where('discount_amount', '>=', $amount);
        });
    }
}
