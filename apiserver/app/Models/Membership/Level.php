<?php

declare(strict_types=1);

namespace App\Models\Membership;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Level Model - Represents a rank within a Stage
 *
 * Each Stage has 4 levels (Bronze, Silver, Gold, Diamond)
 * with team capacity of 5^level_number (5, 25, 125, 625)
 *
 * Unique identification:
 * - full_name: "Premium Gold" (unique across all stages)
 * - global_rank: 1-16 (unique number for easy comparison)
 * - level_number: 1-4 (position within stage)
 */
class Level extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'stage_id',
        'name',
        'full_name',
        'global_rank',
        'level_number',
        'slug',
        'description',
        'team_member_limit',
        'min_direct_referrals',
        'min_active_directs',
        'min_personal_purchase',
        'min_team_sales',
        'validity_days',
        'joining_bonus',
        'purchase_commission',
        'recruitment_commission',
        'depth_commissions',
        'commission_multiplier',
        'level_benefits',
        'badge_icon',
        'badge_color',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'global_rank' => 'integer',
            'level_number' => 'integer',
            'team_member_limit' => 'integer',
            'min_direct_referrals' => 'integer',
            'min_active_directs' => 'integer',
            'min_personal_purchase' => 'integer',
            'min_team_sales' => 'integer',
            'validity_days' => 'integer',
            'joining_bonus' => 'decimal:2',
            'purchase_commission' => 'decimal:2',
            'recruitment_commission' => 'decimal:2',
            'depth_commissions' => 'array',
            'commission_multiplier' => 'decimal:2',
            'level_benefits' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Level $level) {
            if (! $level->uuid) {
                $level->uuid = Str::uuid()->toString();
            }

            if (! $level->slug) {
                $level->slug = Str::slug($level->name);
            }

            // Auto-calculate team_member_limit based on level_number (5^n)
            if (! $level->team_member_limit && $level->level_number) {
                $level->team_member_limit = (int) pow(5, $level->level_number);
            }

            // Generate full_name if not set
            if (! $level->full_name && $level->stage_id) {
                $stage = Stage::find($level->stage_id);
                if ($stage) {
                    $level->full_name = "{$stage->name} {$level->name}";
                }
            }
        });

        static::updating(function (Level $level) {
            // Recalculate team_member_limit if level_number changed
            if ($level->isDirty('level_number')) {
                $level->team_member_limit = (int) pow(5, $level->level_number);
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * Get the stage this level belongs to
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    /**
     * Get all subscriptions at this level
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get users currently at this level
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'level_id');
    }

    // ========================================
    // Level Navigation
    // ========================================

    /**
     * Get next level within same stage
     */
    public function getNextLevel(): ?self
    {
        return static::where('stage_id', $this->stage_id)
            ->where('level_number', $this->level_number + 1)
            ->first();
    }

    /**
     * Get previous level within same stage
     */
    public function getPreviousLevel(): ?self
    {
        if ($this->level_number <= 1) {
            return null;
        }

        return static::where('stage_id', $this->stage_id)
            ->where('level_number', $this->level_number - 1)
            ->first();
    }

    /**
     * Check if this is the first level of the stage
     */
    public function isFirstLevel(): bool
    {
        return $this->level_number === 1;
    }

    /**
     * Check if this is the last level of the stage
     */
    public function isLastLevel(): bool
    {
        return $this->level_number === 4; // Matrix depth is always 4
    }

    /**
     * Get next level by global rank (can cross stages)
     */
    public function getNextGlobalLevel(): ?self
    {
        return static::where('global_rank', $this->global_rank + 1)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get previous level by global rank
     */
    public function getPreviousGlobalLevel(): ?self
    {
        if ($this->global_rank <= 1) {
            return null;
        }

        return static::where('global_rank', $this->global_rank - 1)
            ->where('is_active', true)
            ->first();
    }

    // ========================================
    // Qualification Methods
    // ========================================

    /**
     * Check if user qualifies for this level
     */
    public function checkQualification(array $stats): bool
    {
        // Check direct referrals
        if (($stats['direct_count'] ?? 0) < $this->min_direct_referrals) {
            return false;
        }

        // Check active directs
        if (($stats['active_direct_count'] ?? 0) < $this->min_active_directs) {
            return false;
        }

        // Check personal purchase
        if (($stats['personal_sales'] ?? 0) < $this->min_personal_purchase) {
            return false;
        }

        // Check team sales
        if (($stats['team_sales'] ?? 0) < $this->min_team_sales) {
            return false;
        }

        return true;
    }

    /**
     * Get qualification progress for a user
     */
    public function getQualificationProgress(array $stats): array
    {
        return [
            'direct_referrals' => [
                'current' => $stats['direct_count'] ?? 0,
                'required' => $this->min_direct_referrals,
                'met' => ($stats['direct_count'] ?? 0) >= $this->min_direct_referrals,
            ],
            'active_directs' => [
                'current' => $stats['active_direct_count'] ?? 0,
                'required' => $this->min_active_directs,
                'met' => ($stats['active_direct_count'] ?? 0) >= $this->min_active_directs,
            ],
            'personal_purchase' => [
                'current' => $stats['personal_sales'] ?? 0,
                'required' => $this->min_personal_purchase,
                'met' => ($stats['personal_sales'] ?? 0) >= $this->min_personal_purchase,
            ],
            'team_sales' => [
                'current' => $stats['team_sales'] ?? 0,
                'required' => $this->min_team_sales,
                'met' => ($stats['team_sales'] ?? 0) >= $this->min_team_sales,
            ],
        ];
    }

    // ========================================
    // Commission Methods
    // ========================================

    /**
     * Get commission rate for specific depth
     */
    public function getDepthCommission(int $depth): float
    {
        $commissions = $this->depth_commissions ?? [];

        return (float) ($commissions[(string) $depth] ?? $commissions[$depth] ?? 0);
    }

    /**
     * Apply commission multiplier to base amount
     */
    public function applyMultiplier(int $baseAmount): int
    {
        return (int) round($baseAmount * $this->commission_multiplier);
    }

    /**
     * Get achievement bonus in paisa
     */
    public function getAchievementBonus(): int
    {
        // joining_bonus stores percentage, get from stage's level_achievement_bonus
        return $this->stage?->getLevelAchievementBonus($this->level_number) ?? 0;
    }

    // ========================================
    // Display Helpers
    // ========================================

    /**
     * Get badge HTML/icon
     */
    public function getBadgeAttribute(): array
    {
        return [
            'icon' => $this->badge_icon,
            'color' => $this->badge_color,
            'name' => $this->full_name,
            'rank' => $this->global_rank,
        ];
    }

    /**
     * Get benefit value
     */
    public function getBenefit(string $key, mixed $default = null): mixed
    {
        return $this->level_benefits[$key] ?? $default;
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForStage($query, int $stageId)
    {
        return $query->where('stage_id', $stageId);
    }

    public function scopeByGlobalRank($query)
    {
        return $query->orderBy('global_rank');
    }

    public function scopeByLevelNumber($query)
    {
        return $query->orderBy('level_number');
    }

    // ========================================
    // Static Helpers
    // ========================================

    /**
     * Get level by global rank
     */
    public static function findByGlobalRank(int $rank): ?self
    {
        return static::where('global_rank', $rank)->first();
    }

    /**
     * Get level by full name
     */
    public static function findByFullName(string $fullName): ?self
    {
        return static::where('full_name', $fullName)->first();
    }

    /**
     * Calculate global rank from stage and level numbers
     * Formula: (stage_sort_order - 1) * 4 + level_number
     */
    public static function calculateGlobalRank(int $stageSortOrder, int $levelNumber): int
    {
        return (($stageSortOrder - 1) * 4) + $levelNumber;
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
