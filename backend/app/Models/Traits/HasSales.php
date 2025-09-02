<?php

namespace App\Models\Traits;

use App\Models\Promotion\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasSales
{
    /**
     * Get all sales linked to this model.
     */
    public function sales(): MorphToMany
    {
        return $this->morphToMany(
            Sale::class,
            'target',
            'sale_targets'
        );
    }

    /**
     * Scope: filter sales that are currently active.
     */
    public function scopeWithActiveSales(Builder $query): Builder
    {
        return $query->whereHas('sales', function ($q) {
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
     * Scope: filter sales by action type.
     */
    public function scopeWithSalesOfType(Builder $query, string $actionType): Builder
    {
        return $query->whereHas('sales', function ($q) use ($actionType) {
            $q->where('action_type', $actionType);
        });
    }

    /**
     * Scope: filter sales by minimum discount amount.
     */
    public function scopeWithSalesMinDiscount(Builder $query, int $amount): Builder
    {
        return $query->whereHas('sales', function ($q) use ($amount) {
            $q->where('discount_amount', '>=', $amount);
        });
    }
}
