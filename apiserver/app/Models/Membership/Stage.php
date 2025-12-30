<?php

declare(strict_types=1);

namespace App\Models\Membership;

use App\Services\MoneyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Stage Model - Represents a membership tier in the Affiliate system
 *
 * Each stage contains 4 levels (Bronze, Silver, Gold, Diamond)
 * with a 5^n matrix structure (5 direct, 25, 125, 625 = 780 max team)
 */
class Stage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'base_price',
        'discount',
        'tax_percentage',
        'tax_amount',
        'price',
        'max_team_members',
        'matrix_width',
        'matrix_depth',
        'commission_rates',
        'sponsor_bonus',
        'matching_bonus_percent',
        'matching_bonus_levels',
        'pool_contribution_percent',
        'level_achievement_bonus',
        'upgrade_to_stage_id',
        'upgrade_price_difference',
        'pv',
        'bv',
        'benefits',
        'accessibility',
        'sort_order',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'discount' => 'integer',
            'tax_percentage' => 'integer',
            'tax_amount' => 'integer',
            'price' => 'integer',
            'max_team_members' => 'integer',
            'matrix_width' => 'integer',
            'matrix_depth' => 'integer',
            'commission_rates' => 'array',
            'sponsor_bonus' => 'array',
            'matching_bonus_percent' => 'decimal:2',
            'matching_bonus_levels' => 'integer',
            'pool_contribution_percent' => 'decimal:2',
            'level_achievement_bonus' => 'array',
            'upgrade_price_difference' => 'integer',
            'pv' => 'integer',
            'bv' => 'integer',
            'benefits' => 'array',
            'accessibility' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Stage $stage) {
            if (! $stage->uuid) {
                $stage->uuid = Str::uuid()->toString();
            }

            if (! $stage->slug) {
                $stage->slug = Str::slug($stage->name);
            }

            // Set default matrix configuration
            if (! $stage->matrix_width) {
                $stage->matrix_width = 5;
            }
            if (! $stage->matrix_depth) {
                $stage->matrix_depth = 4;
            }

            // Calculate max team members from matrix config
            $stage->calculateMaxTeamMembers();

            // Calculate pricing
            $stage->calculatePricing();
        });

        static::updating(function (Stage $stage) {
            if ($stage->isDirty(['base_price', 'discount', 'tax_percentage'])) {
                $stage->calculatePricing();
            }

            if ($stage->isDirty(['matrix_width', 'matrix_depth'])) {
                $stage->calculateMaxTeamMembers();
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * Get all levels for this stage (always 4: Bronze, Silver, Gold, Diamond)
     */
    public function levels(): HasMany
    {
        return $this->hasMany(Level::class)->orderBy('level_number');
    }

    /**
     * Get all subscriptions for this stage
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get the stage this can be upgraded to
     */
    public function upgradeToStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'upgrade_to_stage_id');
    }

    /**
     * Get stages that can upgrade to this stage
     */
    public function upgradeFromStages(): HasMany
    {
        return $this->hasMany(Stage::class, 'upgrade_to_stage_id');
    }

    // ========================================
    // Matrix Configuration
    // ========================================

    /**
     * Calculate max team members from matrix config
     * Formula: sum of 5^1 + 5^2 + 5^3 + 5^4 = 780
     */
    public function calculateMaxTeamMembers(): void
    {
        $total = 0;
        for ($i = 1; $i <= $this->matrix_depth; $i++) {
            $total += pow($this->matrix_width, $i);
        }
        $this->max_team_members = $total;
    }

    /**
     * Get team capacity at specific level
     */
    public function getTeamCapacityAtLevel(int $levelNumber): int
    {
        return (int) pow($this->matrix_width, $levelNumber);
    }

    /**
     * Get cumulative team capacity up to and including a level
     */
    public function getCumulativeCapacity(int $levelNumber): int
    {
        $total = 0;
        for ($i = 1; $i <= min($levelNumber, $this->matrix_depth); $i++) {
            $total += pow($this->matrix_width, $i);
        }

        return $total;
    }

    // ========================================
    // Pricing Methods (All in Paisa)
    // ========================================

    /**
     * Calculate pricing fields
     */
    public function calculatePricing(): void
    {
        $baseAfterDiscount = $this->base_price - $this->discount;
        $this->tax_amount = (int) round($baseAfterDiscount * ($this->tax_percentage / 100));
        $this->price = $baseAfterDiscount + $this->tax_amount;
    }

    /**
     * Get price in Rupees
     */
    public function getPriceInRupeesAttribute(): float
    {
        return MoneyService::toRupees($this->price);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return MoneyService::format($this->price);
    }

    // ========================================
    // Commission Configuration
    // ========================================

    /**
     * Get commission rate for a specific depth level (1-4)
     */
    public function getCommissionRate(int $depth): float
    {
        $rates = $this->commission_rates ?? [];

        return (float) ($rates["level_{$depth}"] ?? $rates[(string) $depth] ?? $rates[$depth] ?? 0);
    }

    /**
     * Get sponsor bonus configuration
     */
    public function getSponsorBonusAmount(int $subscriptionPrice): int
    {
        $config = $this->sponsor_bonus ?? [];

        if (empty($config)) {
            return 0;
        }

        $type = $config['type'] ?? 'percent';
        $value = (float) ($config['value'] ?? 0);

        if ($type === 'percent') {
            return (int) round($subscriptionPrice * ($value / 100));
        }

        return (int) $value; // Fixed amount in paisa
    }

    /**
     * Get level achievement bonus for a specific level
     */
    public function getLevelAchievementBonus(int $levelNumber): int
    {
        $bonuses = $this->level_achievement_bonus ?? [];

        return (int) ($bonuses[(string) $levelNumber] ?? $bonuses[$levelNumber] ?? 0);
    }

    // ========================================
    // Level Navigation
    // ========================================

    /**
     * Get first level of this stage
     */
    public function getFirstLevel(): ?Level
    {
        return $this->levels()->where('level_number', 1)->first();
    }

    /**
     * Get level by number (1-4)
     */
    public function getLevelByNumber(int $number): ?Level
    {
        return $this->levels()->where('level_number', $number)->first();
    }

    /**
     * Get last level of this stage
     */
    public function getLastLevel(): ?Level
    {
        return $this->levels()->orderByDesc('level_number')->first();
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // ========================================
    // Static Helpers
    // ========================================

    /**
     * Get the default stage
     */
    public static function getDefault(): ?self
    {
        return static::active()->default()->first()
            ?? static::active()->ordered()->first();
    }

    /**
     * Get next stage for upgrade
     */
    public function getNextStage(): ?self
    {
        return $this->upgradeToStage;
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
