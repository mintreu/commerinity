<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Casts\SaleActionTypeCast;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SaleProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'starts_from',
        'ends_till',
        'action_type',
        'sale_price',
        'discount_amount',
        'end_other_rules',
        'sort_order',
        'target_type',
        'target_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_from' => 'datetime',
            'ends_till' => 'datetime',
            'action_type' => SaleActionTypeCast::class,
            'sale_price' => 'integer',
            'discount_amount' => 'integer',
            'end_other_rules' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Parent sale (optional - can be standalone)
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * The product this sale applies to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Polymorphic target (user, user group, etc.)
     */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    // ==================== SCOPES ====================

    /**
     * Active sale products
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q): void {
            $q->whereNull('starts_from')
                ->orWhere('starts_from', '<=', now());
        })->where(function ($q): void {
            $q->whereNull('ends_till')
                ->orWhere('ends_till', '>=', now());
        });
    }

    /**
     * Sale products for a specific product
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Sale products for a specific target
     */
    public function scopeForTarget(Builder $query, Model $target): Builder
    {
        return $query->where('target_type', $target::class)
            ->where('target_id', $target->getKey());
    }

    /**
     * Ordered by priority
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if this sale product is currently active
     */
    public function isActive(): bool
    {
        $now = now();

        // Check own validity period
        if ($this->starts_from && $now->lt($this->starts_from)) {
            return false;
        }

        if ($this->ends_till && $now->gt($this->ends_till)) {
            return false;
        }

        // If linked to a sale, check sale validity too
        if ($this->sale_id && $this->sale && ! $this->sale->isActive()) {
            return false;
        }

        return true;
    }

    /**
     * Get the effective action type (own or from parent sale)
     */
    public function getEffectiveActionType(): ?SaleActionTypeCast
    {
        if ($this->action_type instanceof SaleActionTypeCast) {
            return $this->action_type;
        }

        return $this->sale?->action_type;
    }

    /**
     * Get the effective discount amount (own or from parent sale)
     */
    public function getEffectiveDiscountAmount(): int
    {
        if ($this->discount_amount > 0) {
            return $this->discount_amount;
        }

        return $this->sale?->discount_amount ?? 0;
    }

    /**
     * Get the final sale price
     *
     * @param  int  $originalPrice  Original price in paise
     * @return int Final price in paise
     */
    public function getFinalPrice(int $originalPrice): int
    {
        // If sale_price is set, use it directly
        if ($this->sale_price > 0) {
            return $this->sale_price;
        }

        // Otherwise calculate based on action type and discount
        $actionType = $this->getEffectiveActionType();
        $discountAmount = $this->getEffectiveDiscountAmount();

        if (! $actionType) {
            return $originalPrice;
        }

        return $actionType->calculatePrice($originalPrice, $discountAmount);
    }

    /**
     * Get the discount amount
     *
     * @param  int  $originalPrice  Original price in paise
     * @return int Discount amount in paise
     */
    public function getDiscountAmount(int $originalPrice): int
    {
        $finalPrice = $this->getFinalPrice($originalPrice);

        return max(0, $originalPrice - $finalPrice);
    }

    /**
     * Get discount percentage
     *
     * @param  int  $originalPrice  Original price in paise
     * @return float Percentage discount (0-100)
     */
    public function getDiscountPercentage(int $originalPrice): float
    {
        if ($originalPrice <= 0) {
            return 0;
        }

        $discount = $this->getDiscountAmount($originalPrice);

        return round(($discount / $originalPrice) * 100, 2);
    }
}
