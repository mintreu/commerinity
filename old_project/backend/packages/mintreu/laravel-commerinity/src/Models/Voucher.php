<?php

namespace Mintreu\LaravelCommerinity\Models;


use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Mintreu\LaravelCommerinity\Casts\VoucherActionTypeCast;
use Mintreu\LaravelCommerinity\Casts\VoucherConditionMatchingCast;
use Mintreu\LaravelCommerinity\Database\Factories\VoucherFactory;
use Mintreu\LaravelMoney\Casts\LaravelMoneyCast;
use Mintreu\Toolkit\Traits\HasPackageModelFactory;

class Voucher extends Model
{
    /** @use HasFactory<VoucherFactory> */
    use HasPackageModelFactory;


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
        'condition_type' => VoucherConditionMatchingCast::class,
        'action_type' => VoucherActionTypeCast::class,
        'conditions' => 'array',
        'end_other_rules' => 'boolean',
        'discount_amount' => LaravelMoneyCast::class,
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




    /**
     * Generic polymorphic relation to targets.
     */
    public function targets(): MorphToMany
    {
        $targetModel = config('laravel-commerinity.voucher.targets')[0] ?? null;

        if (! $targetModel) {
            throw new \RuntimeException(
                "No voucher target models defined in config/laravel-commerinity.php"
            );
        }

        return $this->morphedByMany(
            $targetModel,      // Related model
            'target',          // <-- morph name, matches "morphs('target')"
            'voucher_targets',    // Pivot table
            'voucher_id',         // FK on pivot to Sale
            'target_id'        // FK on pivot to related model
        );
    }
    public function scopeWithoutTargets(Builder $query): Builder
    {
        return $query->whereDoesntHave('targets');
    }

    /**
     * Scope: sales for a specific target model instance.
     */
    public function scopeForTarget(Builder $query, Model $target): Builder
    {
        return $query->whereHas('targets', function ($q) use ($target) {
            $q->where('target_type', get_class($target))
                ->where('target_id', $target->getKey());
        });
    }

    /**
     * Scope: sales for a specific target type (class).
     */
    public function scopeForTargetType(Builder $query, string $targetClass): Builder
    {
        return $query->whereHas('targets', function ($q) use ($targetClass) {
            $q->where('target_type', $targetClass);
        });
    }


    public function coupons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VoucherCode::class);
    }

    public function coupon(): HasOne
    {
        return $this->coupons()->where('is_primary', 1)->limit(1);
    }







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
