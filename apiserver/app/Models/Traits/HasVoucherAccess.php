<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Ecommerce\Voucher;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * HasVoucherAccess Trait
 *
 * Add this trait to any model (Product, Category, User, etc.) to enable
 * polymorphic relationship with Vouchers.
 *
 * Usage:
 *   use HasVoucherAccess;
 *   protected static function bootHasVoucherAccess(): void { ... }
 */
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
        return $query->whereHas('vouchers', function ($q): void {
            $q->where('status', true)
                ->where(function ($q2): void {
                    $now = now();
                    $q2->whereNull('starts_from')->orWhere('starts_from', '<=', $now);
                })
                ->where(function ($q2): void {
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
        return $query->whereHas('vouchers', function ($q) use ($actionType): void {
            $q->where('action_type', $actionType);
        });
    }

    /**
     * Scope: filter vouchers by minimum discount amount.
     */
    public function scopeWithVouchersMinDiscount(Builder $query, int $amount): Builder
    {
        return $query->whereHas('vouchers', function ($q) use ($amount): void {
            $q->where('discount_amount', '>=', $amount);
        });
    }

    /**
     * Check if this model has any active vouchers.
     */
    public function hasActiveVouchers(): bool
    {
        return $this->vouchers()->active()->exists();
    }

    /**
     * Get all usable vouchers for this model.
     */
    public function getUsableVouchers(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->vouchers()
            ->where('status', true)
            ->where(function ($q): void {
                $q->whereNull('starts_from')->orWhere('starts_from', '<=', now());
            })
            ->where(function ($q): void {
                $q->whereNull('ends_till')->orWhere('ends_till', '>=', now());
            })
            ->whereColumn('times_used', '<', 'coupon_usage_limit')
            ->get();
    }
}
