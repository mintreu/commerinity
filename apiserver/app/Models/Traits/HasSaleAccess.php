<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Ecommerce\Sale;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * HasSaleAccess Trait
 *
 * Add this trait to any model (Product, Category, etc.) to enable
 * polymorphic relationship with Sales.
 *
 * Usage:
 *   use HasSaleAccess;
 *   protected static function bootHasSaleAccess(): void { ... }
 */
trait HasSaleAccess
{
    /**
     * Get all sales linked to this model.
     */
    public function sales(): MorphToMany
    {
        return $this->morphToMany(
            Sale::class,
            'target',
            'sale_targets',
            'target_id',
            'sale_id'
        );
    }

    /**
     * Scope: filter sales that are currently active.
     */
    public function scopeWithActiveSales(Builder $query): Builder
    {
        return $query->whereHas('sales', function ($q): void {
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
     * Scope: filter sales by action type.
     */
    public function scopeWithSalesOfType(Builder $query, string $actionType): Builder
    {
        return $query->whereHas('sales', function ($q) use ($actionType): void {
            $q->where('action_type', $actionType);
        });
    }

    /**
     * Scope: filter sales by minimum discount amount.
     */
    public function scopeWithSalesMinDiscount(Builder $query, int $amount): Builder
    {
        return $query->whereHas('sales', function ($q) use ($amount): void {
            $q->where('discount_amount', '>=', $amount);
        });
    }

    /**
     * Check if this model has any active sales.
     */
    public function hasActiveSales(): bool
    {
        return $this->sales()->active()->exists();
    }

    /**
     * Get the active sale with highest discount.
     */
    public function getBestActiveSale(): ?Sale
    {
        return $this->sales()
            ->active()
            ->orderBy('discount_amount', 'desc')
            ->first();
    }
}
