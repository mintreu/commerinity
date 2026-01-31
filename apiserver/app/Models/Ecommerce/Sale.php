<?php

declare(strict_types=1);

namespace App\Models\Ecommerce;

use App\Casts\ConditionMatchingCast;
use App\Casts\SaleActionTypeCast;
use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
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
        'target_user_types',
        'target_wholesale_only',
    ];

    protected function casts(): array
    {
        return [
            'starts_from' => 'datetime',
            'ends_till' => 'datetime',
            'status' => 'boolean',
            'conditions' => 'array',
            'end_other_rules' => 'boolean',
            'condition_type' => ConditionMatchingCast::class,
            'action_type' => SaleActionTypeCast::class,
            'discount_amount' => 'integer',
            'sort_order' => 'integer',
            'target_user_types' => 'array',
            'target_wholesale_only' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Sale $sale): void {
            if (empty($sale->uuid)) {
                $sale->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Sale products (individual product overrides)
     */
    public function saleProducts(): HasMany
    {
        return $this->hasMany(SaleProduct::class);
    }

    /**
     * Category targets
     */
    public function categories(): MorphToMany
    {
        return $this->morphedByMany(
            Category::class,
            'target',
            'sale_targets',
            'sale_id',
            'target_id'
        );
    }

    /**
     * Product targets (direct product selection)
     */
    public function products(): MorphToMany
    {
        return $this->morphedByMany(
            Product::class,
            'target',
            'sale_targets',
            'sale_id',
            'target_id'
        );
    }

    /**
     * Stage targets (membership tier)
     */
    public function stages(): MorphToMany
    {
        return $this->morphedByMany(
            Stage::class,
            'target',
            'sale_targets',
            'sale_id',
            'target_id'
        );
    }

    /**
     * Level targets (membership rank)
     */
    public function levels(): MorphToMany
    {
        return $this->morphedByMany(
            Level::class,
            'target',
            'sale_targets',
            'sale_id',
            'target_id'
        );
    }

    /**
     * User targets (specific users)
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            User::class,
            'target',
            'sale_targets',
            'sale_id',
            'target_id'
        );
    }

    // ==================== SCOPES ====================

    /**
     * Scope to active sales
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true)
            ->where('starts_from', '<=', now())
            ->where('ends_till', '>=', now());
    }

    /**
     * Scope to sales without specific targets (site-wide)
     */
    public function scopeWithoutTargets(Builder $query): Builder
    {
        return $query->whereNotExists(function ($sub): void {
            $sub->select(DB::raw(1))
                ->from('sale_targets')
                ->whereColumn('sale_targets.sale_id', 'sales.id');
        });
    }

    /**
     * Scope for a specific target instance
     */
    public function scopeForTarget(Builder $query, Model $target): Builder
    {
        return $query->whereExists(function ($sub) use ($target): void {
            $sub->select(DB::raw(1))
                ->from('sale_targets')
                ->whereColumn('sale_targets.sale_id', 'sales.id')
                ->where('target_type', $target::class)
                ->where('target_id', $target->getKey());
        });
    }

    /**
     * Scope for a specific target type
     */
    public function scopeForTargetType(Builder $query, string $targetClass): Builder
    {
        return $query->whereExists(function ($sub) use ($targetClass): void {
            $sub->select(DB::raw(1))
                ->from('sale_targets')
                ->whereColumn('sale_targets.sale_id', 'sales.id')
                ->where('target_type', $targetClass);
        });
    }

    /**
     * Scope ordered by priority
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if sale is currently active
     */
    public function isActive(): bool
    {
        return $this->status && now()->between($this->starts_from, $this->ends_till);
    }

    /**
     * Check if sale is expired
     */
    public function isExpired(): bool
    {
        return $this->ends_till && now()->gt($this->ends_till);
    }

    /**
     * Check if sale hasn't started yet
     */
    public function isPending(): bool
    {
        return $this->starts_from && now()->lt($this->starts_from);
    }

    /**
     * Calculate the discounted price for a given original price
     *
     * @param  int  $originalPrice  Original price in paise
     * @return int Final price in paise
     */
    public function calculatePrice(int $originalPrice): int
    {
        if (! $this->action_type instanceof SaleActionTypeCast) {
            return $originalPrice;
        }

        return $this->action_type->calculatePrice($originalPrice, $this->discount_amount);
    }

    /**
     * Calculate the discount amount for a given original price
     *
     * @param  int  $originalPrice  Original price in paise
     * @return int Discount amount in paise
     */
    public function calculateDiscount(int $originalPrice): int
    {
        if (! $this->action_type instanceof SaleActionTypeCast) {
            return 0;
        }

        return $this->action_type->calculateDiscount($originalPrice, $this->discount_amount);
    }

    /**
     * Get formatted discount display
     */
    public function getDiscountDisplay(): string
    {
        if (! $this->action_type instanceof SaleActionTypeCast) {
            return '';
        }

        return $this->action_type->formatValue($this->discount_amount);
    }

    /**
     * Check if this sale applies to a specific product
     */
    public function appliesTo(Product $product): bool
    {
        // Product/Category targeting only affects product eligibility
        $hasProductTargets = $this->products()->exists() || $this->categories()->exists();

        if ($hasProductTargets) {
            if ($this->products()->where('products.id', $product->id)->exists()) {
                return true;
            }

            if ($product->category_id && $this->categories()->where('categories.id', $product->category_id)->exists()) {
                return true;
            }

            return false;
        }

        return true;
    }
}
