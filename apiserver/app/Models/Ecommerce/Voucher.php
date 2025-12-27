<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Casts\ConditionMatchingCast;
use App\Casts\VoucherActionTypeCast;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Voucher extends Model
{
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
        'min_cart_value',
        'min_quantity',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'starts_from' => 'date',
            'ends_till' => 'date',
            'status' => 'boolean',
            'condition_type' => ConditionMatchingCast::class,
            'conditions' => 'array',
            'end_other_rules' => 'boolean',
            'action_type' => VoucherActionTypeCast::class,
            'discount_amount' => 'integer',
            'discount_quantity' => 'integer',
            'apply_to_shipping' => 'boolean',
            'free_shipping' => 'boolean',
            'min_cart_value' => 'integer',
            'min_quantity' => 'integer',
            'usage_per_customer' => 'integer',
            'coupon_usage_limit' => 'integer',
            'times_used' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * All coupon codes for this voucher
     */
    public function codes(): HasMany
    {
        return $this->hasMany(VoucherCode::class);
    }

    /**
     * Primary coupon code
     */
    public function primaryCode(): HasOne
    {
        return $this->hasOne(VoucherCode::class)->where('is_primary', true);
    }

    /**
     * Polymorphic targets (categories, products, etc.)
     */
    public function targets(): MorphToMany
    {
        return $this->morphedByMany(
            Category::class,
            'target',
            'voucher_targets',
            'voucher_id',
            'target_id'
        );
    }

    /**
     * Category targets
     */
    public function categories(): MorphToMany
    {
        return $this->morphedByMany(
            Category::class,
            'target',
            'voucher_targets',
            'voucher_id',
            'target_id'
        );
    }

    /**
     * Product targets
     */
    public function products(): MorphToMany
    {
        return $this->morphedByMany(
            Product::class,
            'target',
            'voucher_targets',
            'voucher_id',
            'target_id'
        );
    }

    // ==================== SCOPES ====================

    /**
     * Active vouchers
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true)
            ->where('starts_from', '<=', now())
            ->where('ends_till', '>=', now());
    }

    /**
     * Vouchers without specific targets (site-wide)
     */
    public function scopeWithoutTargets(Builder $query): Builder
    {
        return $query->whereDoesntHave('targets');
    }

    /**
     * Vouchers for a specific target
     */
    public function scopeForTarget(Builder $query, Model $target): Builder
    {
        return $query->whereHas('targets', function ($q) use ($target): void {
            $q->where('target_type', $target::class)
                ->where('target_id', $target->getKey());
        });
    }

    /**
     * Available vouchers (not fully used)
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(function ($q): void {
            $q->where('coupon_usage_limit', 0) // Unlimited
                ->orWhereColumn('times_used', '<', 'coupon_usage_limit');
        });
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
     * Check if voucher is currently active
     */
    public function isActive(): bool
    {
        return $this->status && now()->between($this->starts_from, $this->ends_till);
    }

    /**
     * Check if voucher is expired
     */
    public function isExpired(): bool
    {
        return $this->ends_till && now()->gt($this->ends_till);
    }

    /**
     * Check if voucher can be used (active + not fully consumed)
     */
    public function canBeUsed(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        // Check global usage limit
        if ($this->coupon_usage_limit > 0 && $this->times_used >= $this->coupon_usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if voucher has unlimited uses
     */
    public function hasUnlimitedUses(): bool
    {
        return $this->coupon_usage_limit === 0;
    }

    /**
     * Get remaining uses
     */
    public function getRemainingUses(): int
    {
        if ($this->hasUnlimitedUses()) {
            return PHP_INT_MAX;
        }

        return max(0, $this->coupon_usage_limit - $this->times_used);
    }

    /**
     * Check if cart meets minimum requirements
     *
     * @param  int  $cartTotal  Cart total in paise
     * @param  int  $cartQuantity  Total quantity in cart
     */
    public function meetsMinimumRequirements(int $cartTotal, int $cartQuantity): bool
    {
        if ($this->min_cart_value > 0 && $cartTotal < $this->min_cart_value) {
            return false;
        }

        if ($this->min_quantity > 0 && $cartQuantity < $this->min_quantity) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount for cart
     *
     * @param  int  $cartTotal  Cart total in paise
     * @return int Discount amount in paise
     */
    public function calculateDiscount(int $cartTotal): int
    {
        if (! $this->action_type instanceof VoucherActionTypeCast) {
            return 0;
        }

        if ($this->action_type->isCartLevel()) {
            return $this->action_type->calculateCartDiscount($cartTotal, $this->discount_amount);
        }

        return 0;
    }

    /**
     * Calculate discount for an item
     *
     * @param  int  $itemPrice  Item price in paise
     * @return int Discount amount in paise
     */
    public function calculateItemDiscount(int $itemPrice): int
    {
        if (! $this->action_type instanceof VoucherActionTypeCast) {
            return 0;
        }

        if ($this->action_type->isItemLevel()) {
            return $this->action_type->calculateItemDiscount($itemPrice, $this->discount_amount);
        }

        return 0;
    }

    /**
     * Increment usage counter
     */
    public function incrementUsage(): bool
    {
        return $this->increment('times_used');
    }

    /**
     * Check if voucher applies to a specific product
     */
    public function appliesTo(Product $product): bool
    {
        // Check if product is directly targeted
        if ($this->products()->where('products.id', $product->id)->exists()) {
            return true;
        }

        // Check if product's category is targeted
        if ($product->category_id && $this->categories()->where('categories.id', $product->category_id)->exists()) {
            return true;
        }

        // Check if this is a site-wide voucher (no targets)
        if (! $this->targets()->exists()) {
            return true;
        }

        return false;
    }
}
