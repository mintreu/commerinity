<?php

declare(strict_types=1);

namespace App\Models\Mlm;

use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * MlmGenealogy Model - Tracks MLM network stats and matrix placement
 *
 * NOTE: Sponsor/referral tree is managed via users.parent_id (no duplication here)
 * This table stores:
 * - Matrix placement (when spillover differs from parent_id)
 * - Team counts per level (1-4)
 * - Sales volumes
 * - Current stage/level progression
 */
class MlmGenealogy extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static function newFactory(): \Database\Factories\MlmGenealogyFactory
    {
        return \Database\Factories\MlmGenealogyFactory::new();
    }

    protected $table = 'mlm_genealogy';

    protected $fillable = [
        'uuid',
        'user_id',
        'placement_parent_id',
        'placement_position',
        'depth',
        'direct_count',
        'active_direct_count',
        'level_1_count',
        'level_2_count',
        'level_3_count',
        'level_4_count',
        'total_team_count',
        'active_team_count',
        'personal_sales',
        'level_1_sales',
        'level_2_sales',
        'level_3_sales',
        'level_4_sales',
        'total_team_sales',
        'personal_pv',
        'team_pv',
        'current_stage_id',
        'current_level_id',
        'highest_level_id',
        'is_active',
        'activated_at',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'placement_position' => 'integer',
            'depth' => 'integer',
            'direct_count' => 'integer',
            'active_direct_count' => 'integer',
            'level_1_count' => 'integer',
            'level_2_count' => 'integer',
            'level_3_count' => 'integer',
            'level_4_count' => 'integer',
            'total_team_count' => 'integer',
            'active_team_count' => 'integer',
            'personal_sales' => 'integer',
            'level_1_sales' => 'integer',
            'level_2_sales' => 'integer',
            'level_3_sales' => 'integer',
            'level_4_sales' => 'integer',
            'total_team_sales' => 'integer',
            'personal_pv' => 'integer',
            'team_pv' => 'integer',
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MlmGenealogy $genealogy) {
            if (! $genealogy->uuid) {
                $genealogy->uuid = Str::uuid()->toString();
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * Get the user this genealogy belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the placement parent (where placed in matrix tree)
     * NOTE: This can differ from user.parent_id when spillover occurs
     */
    public function placementParent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'placement_parent_id');
    }

    /**
     * Get placement children (users placed under this user in matrix)
     */
    public function placementChildren(): HasMany
    {
        return $this->hasMany(MlmGenealogy::class, 'placement_parent_id', 'user_id');
    }

    /**
     * Get current stage
     */
    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'current_stage_id');
    }

    /**
     * Get current level
     */
    public function currentLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }

    /**
     * Get highest level achieved
     */
    public function highestLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'highest_level_id');
    }

    /**
     * Get all commissions for this genealogy
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(MlmCommission::class, 'genealogy_id');
    }

    // ========================================
    // Tree Navigation (uses User.parent_id)
    // ========================================

    /**
     * Get upline chain via user.parent relationship
     * Uses the adjacency list from users table
     */
    public function getUpline(int $maxLevels = 4): Collection
    {
        $uplines = collect();
        $current = $this->user->parent;
        $level = 0;

        while ($current && $level < $maxLevels) {
            $genealogy = static::forUser($current->id);
            if ($genealogy) {
                $uplines->push($genealogy->load(['user', 'currentLevel']));
            }
            $current = $current->parent;
            $level++;
        }

        return $uplines;
    }

    /**
     * Get direct referrals (users whose parent_id = this user)
     */
    public function getDirectReferrals(): Collection
    {
        return static::whereHas('user', function ($query) {
            $query->where('parent_id', $this->user_id);
        })->with(['user', 'currentLevel'])->get();
    }

    /**
     * Get downline at specific depth level
     * Uses recursive relationship from User model
     */
    public function getDownlineAtLevel(int $level): Collection
    {
        // Use User's recursive relationships (HasRecursiveRelationships trait)
        $descendants = $this->user->descendants()->get();

        return static::whereIn('user_id', $descendants->pluck('id'))
            ->whereHas('user', function ($query) {
                // Filter by depth relative to this user
            })
            ->with(['user', 'currentLevel'])
            ->get();
    }

    // ========================================
    // Placement Methods (Matrix)
    // ========================================

    /**
     * Find available placement position under a user
     * Returns [placement_parent_id, position] or null if full
     */
    public function findAvailablePosition(int $maxWidth = 5): ?array
    {
        // Check if current user has available slots
        $childCount = $this->placementChildren()->count();

        if ($childCount < $maxWidth) {
            return [$this->user_id, $childCount + 1];
        }

        // BFS to find first available slot in tree
        $queue = $this->placementChildren()->orderBy('placement_position')->get();

        foreach ($queue as $child) {
            $result = $child->findAvailablePosition($maxWidth);
            if ($result) {
                return $result;
            }
        }

        return null; // Tree is full
    }

    /**
     * Check if placement tree is full (reached max capacity)
     */
    public function isPlacementTreeFull(int $maxTeamMembers = 780): bool
    {
        return $this->total_team_count >= $maxTeamMembers;
    }

    // ========================================
    // Counter Methods
    // ========================================

    /**
     * Recalculate all counters for this user
     * Uses User.parent_id relationships
     */
    public function recalculateCounters(): void
    {
        // Direct referrals (users with parent_id = this user)
        $this->direct_count = User::where('parent_id', $this->user_id)->count();
        $this->active_direct_count = User::where('parent_id', $this->user_id)
            ->whereHas('genealogy', fn ($q) => $q->where('is_active', true))
            ->count();

        // Use User's recursive descendants for team counts
        $descendants = $this->user->descendants()->get();
        $descendantIds = $descendants->pluck('id');

        // Get genealogy records for all descendants
        $descendantGenealogies = static::whereIn('user_id', $descendantIds)->get();

        // Calculate depth relative to this user
        $this->level_1_count = 0;
        $this->level_2_count = 0;
        $this->level_3_count = 0;
        $this->level_4_count = 0;
        $this->active_team_count = 0;

        foreach ($descendantGenealogies as $desc) {
            $relativeDepth = $desc->depth - $this->depth;

            if ($relativeDepth === 1) {
                $this->level_1_count++;
            } elseif ($relativeDepth === 2) {
                $this->level_2_count++;
            } elseif ($relativeDepth === 3) {
                $this->level_3_count++;
            } elseif ($relativeDepth === 4) {
                $this->level_4_count++;
            }

            if ($desc->is_active) {
                $this->active_team_count++;
            }
        }

        $this->total_team_count = $this->level_1_count + $this->level_2_count
            + $this->level_3_count + $this->level_4_count;

        $this->save();
    }

    /**
     * Increment team counts for all uplines when new member joins
     */
    public static function incrementUplineCounters(int $newMemberId): void
    {
        $newMember = static::forUser($newMemberId);
        if (! $newMember) {
            return;
        }

        // Walk up the parent chain
        foreach ($newMember->getUpline(4) as $index => $upline) {
            $relativeDepth = $index + 1;

            if ($relativeDepth <= 4) {
                $levelField = "level_{$relativeDepth}_count";
                $upline->increment($levelField);
                $upline->increment('total_team_count');

                if ($newMember->is_active) {
                    $upline->increment('active_team_count');
                }
            }

            // Update direct count for immediate parent
            if ($relativeDepth === 1) {
                $upline->increment('direct_count');
                if ($newMember->is_active) {
                    $upline->increment('active_direct_count');
                }
            }
        }
    }

    // ========================================
    // Sales Volume Methods
    // ========================================

    /**
     * Add personal sales and propagate to uplines
     */
    public function addSales(int $amountInPaisa, int $pv = 0): void
    {
        // Update personal
        $this->increment('personal_sales', $amountInPaisa);
        $this->increment('personal_pv', $pv);

        // Propagate to uplines (via User.parent_id chain)
        foreach ($this->getUpline(4) as $index => $upline) {
            $level = $index + 1;
            if ($level <= 4) {
                $upline->increment("level_{$level}_sales", $amountInPaisa);
                $upline->increment('total_team_sales', $amountInPaisa);
                $upline->increment('team_pv', $pv);
            }
        }

        $this->touch();
        $this->update(['last_activity_at' => now()]);
    }

    // ========================================
    // Status Methods
    // ========================================

    /**
     * Activate this genealogy record
     */
    public function activate(): void
    {
        $this->is_active = true;
        $this->activated_at = now();
        $this->save();
    }

    /**
     * Deactivate this genealogy record
     */
    public function deactivate(): void
    {
        $this->is_active = false;
        $this->save();
    }

    /**
     * Update current stage and level
     */
    public function updateStageLevel(int $stageId, int $levelId): void
    {
        $this->current_stage_id = $stageId;
        $this->current_level_id = $levelId;

        // Track highest level
        if (! $this->highest_level_id) {
            $this->highest_level_id = $levelId;
        } else {
            $currentHighest = Level::find($this->highest_level_id);
            $newLevel = Level::find($levelId);

            if ($newLevel && $currentHighest && $newLevel->global_rank > $currentHighest->global_rank) {
                $this->highest_level_id = $levelId;
            }
        }

        $this->save();
    }

    // ========================================
    // Query Scopes
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAtStage($query, int $stageId)
    {
        return $query->where('current_stage_id', $stageId);
    }

    public function scopeAtLevel($query, int $levelId)
    {
        return $query->where('current_level_id', $levelId);
    }

    // ========================================
    // Static Helpers
    // ========================================

    /**
     * Get genealogy for a user
     */
    public static function forUser(int $userId): ?self
    {
        return static::where('user_id', $userId)->first();
    }

    /**
     * Create genealogy for new user
     * Depth is calculated from user.parent_id chain
     */
    public static function createForUser(
        int $userId,
        ?int $placementParentId = null,
        int $placementPosition = 1
    ): self {
        $user = User::find($userId);
        $depth = 0;

        // Calculate depth from parent chain
        if ($user?->parent_id) {
            $parentGenealogy = static::forUser($user->parent_id);
            $depth = $parentGenealogy ? $parentGenealogy->depth + 1 : 1;

            // Default placement to parent if not specified
            $placementParentId = $placementParentId ?? $user->parent_id;
        }

        return static::create([
            'user_id' => $userId,
            'placement_parent_id' => $placementParentId,
            'placement_position' => $placementPosition,
            'depth' => $depth,
            'is_active' => true,
            'activated_at' => now(),
        ]);
    }

    // ========================================
    // Route Model Binding
    // ========================================

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
