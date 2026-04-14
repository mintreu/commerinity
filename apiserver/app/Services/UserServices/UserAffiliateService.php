<?php

declare(strict_types=1);

namespace App\Services\UserServices;

use App\Casts\CommissionStatusCast;
use App\Casts\CommissionTypeCast;
use App\Models\Affiliate\AffiliateCommission;
use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * UserAffiliateService - Handles Affiliate network operations with optimized queries
 *
 * Responsibilities:
 * - Find available slot for new user (BFS algorithm)
 * - Place user in Affiliate tree
 * - Calculate and distribute commissions
 * - Track originator (agent/advisor) commissions
 */
final class UserAffiliateService implements \App\Contracts\Services\UserAffiliateServiceInterface
{
    private readonly int $maxDirectChildren;

    private readonly int $maxDepthLevels;

    public function __construct()
    {
        $this->maxDirectChildren = config('affiliate.matrix.max_direct_children', 5);
        $this->maxDepthLevels = config('affiliate.matrix.max_depth_levels', 4);
    }

    /**
     * Find available slot for new user using BFS
     *
     * If target user has free slot, place there.
     * Otherwise, find first descendant with free slot.
     *
     * @param  User  $targetUser  The user to place under (sponsor)
     * @return array{parent_id: int, position: int}|null
     */
    public function findAvailableSlot(User $targetUser): ?array
    {
        // Check if target user has available slot
        $childCount = $this->getDirectChildrenCount($targetUser->id);

        if ($childCount < $this->maxDirectChildren) {
            return [
                'parent_id' => $targetUser->id,
                'position' => $childCount + 1,
            ];
        }

        // BFS to find first descendant with available slot
        // Use optimized query to get tree level by level
        $queue = collect([$targetUser->id]);
        $visited = collect([$targetUser->id]);

        while ($queue->isNotEmpty()) {
            // Get all children counts for current level in single query
            $currentLevelIds = $queue->toArray();
            $queue = collect();

            // Optimized: Get children counts for all users at once
            $childrenCounts = $this->getChildrenCountsForUsers($currentLevelIds);

            // Get all children for current level
            $children = User::whereIn('parent_id', $currentLevelIds)
                ->select('id', 'parent_id')
                ->get();

            // Check each parent for available slot
            foreach ($currentLevelIds as $parentId) {
                $count = $childrenCounts[$parentId] ?? 0;
                if ($count < $this->maxDirectChildren) {
                    return [
                        'parent_id' => $parentId,
                        'position' => $count + 1,
                    ];
                }
            }

            // Add children to queue for next level
            foreach ($children as $child) {
                if (! $visited->contains($child->id)) {
                    $queue->push($child->id);
                    $visited->push($child->id);
                }
            }
        }

        return null; // Tree is full
    }

    /**
     * Place new user in Affiliate tree
     *
     * @param  User  $newUser  The new user to place
     * @param  User|null  $sponsor  The referring user (parent_id)
     * @param  User|null  $originator  Agent/Advisor who recruited (originator morph)
     */
    public function placeUser(User $newUser, ?User $sponsor = null, ?User $originator = null): AffiliateGenealogy
    {
        return DB::transaction(function () use ($newUser, $sponsor, $originator) {
            $placementParentId = null;
            $position = 1;
            $depth = 0;

            if ($sponsor) {
                // Find placement position FIRST (before setting parent_id to get correct count)
                // Position may differ from sponsor if sponsor is full (spillover)
                $slot = $this->findAvailableSlot($sponsor);
                if ($slot) {
                    $placementParentId = $slot['parent_id'];
                    $position = $slot['position'];
                }

                // Now update user's parent_id (referral relationship)
                $newUser->update(['parent_id' => $sponsor->id]);

                // Calculate depth
                $parentGenealogy = AffiliateGenealogy::forUser($sponsor->id);
                $depth = $parentGenealogy ? $parentGenealogy->depth + 1 : 1;
            }

            // Set originator if provided
            if ($originator) {
                $newUser->update([
                    'originator_type' => get_class($originator),
                    'originator_id' => $originator->id,
                ]);
            }

            // Create genealogy record
            $genealogy = AffiliateGenealogy::create([
                'user_id' => $newUser->id,
                'placement_parent_id' => $placementParentId,
                'placement_position' => $position,
                'depth' => $depth,
                'is_active' => true,
                'activated_at' => now(),
            ]);

            // Update upline counters
            if ($sponsor) {
                $this->incrementUplineCounters($newUser->id);

                $sponsor->notify(new GeneralNotification(
                    title: 'New Referral Joined',
                    message: "{$newUser->name} joined your network.",
                    actionUrl: rtrim((string) config('app.client_url'), '/').'/network',
                    actionText: 'View Network',
                    channels: ['database', 'push', 'mail'],
                    type: 'success',
                ));
            }

            if ($originator && $originator->id !== $sponsor?->id) {
                $originator->notify(new GeneralNotification(
                    title: 'New Originated Member Joined',
                    message: "{$newUser->name} joined through your referral flow.",
                    actionUrl: rtrim((string) config('app.client_url'), '/').'/network',
                    actionText: 'View Network',
                    channels: ['database', 'push', 'mail'],
                    type: 'info',
                ));
            }

            return $genealogy;
        });
    }

    /**
     * Calculate and distribute commissions for a purchase/subscription
     *
     * @param  int  $userId  User who made the purchase
     * @param  int  $amountInPaisa  Purchase amount
     * @param  mixed  $commissionable  The model triggering commission (Subscription, Order, etc.)
     * @return Collection<AffiliateCommission>
     */
    public function distributeCommissions(int $userId, int $amountInPaisa, $commissionable): Collection
    {
        $commissions = collect();

        $genealogy = AffiliateGenealogy::forUser($userId);
        if (! $genealogy) {
            return $commissions;
        }

        // Get upline up to 4 levels with optimized query
        $uplines = $this->getUplineWithLevels($userId, $this->maxDepthLevels);

        // Get commission rates from config
        $rates = config('affiliate.commission_rates', [
            1 => 10.0, // Level 1: 10%
            2 => 5.0,  // Level 2: 5%
            3 => 3.0,  // Level 3: 3%
            4 => 2.0,  // Level 4: 2%
        ]);

        DB::transaction(function () use ($uplines, $amountInPaisa, $commissionable, $rates, &$commissions, $userId) {
            foreach ($uplines as $level => $uplineUser) {
                $rate = $rates[$level] ?? 0;
                if ($rate <= 0) {
                    continue;
                }

                $uplineGenealogy = AffiliateGenealogy::forUser($uplineUser->id);
                if (! $uplineGenealogy || ! $uplineGenealogy->is_active) {
                    continue;
                }

                $commissionAmount = (int) round($amountInPaisa * ($rate / 100));

                $commission = AffiliateCommission::create([
                    'user_id' => $uplineUser->id,
                    'genealogy_id' => $uplineGenealogy->id,
                    'from_user_id' => $userId,
                    'commissionable_type' => get_class($commissionable),
                    'commissionable_id' => $commissionable->getKey(),
                    'type' => CommissionTypeCast::LEVEL_COMMISSION->value,
                    'level' => $level,
                    'rate_percent' => $rate,
                    'base_amount' => $amountInPaisa,
                    'gross_amount' => $commissionAmount,
                    'net_amount' => $commissionAmount,
                    'status' => CommissionStatusCast::PENDING,
                    'description' => "Level {$level} commission ({$rate}%)",
                ]);

                $commissions->push($commission);

                // Update genealogy sales
                $levelField = "level_{$level}_sales";
                $uplineGenealogy->increment($levelField, $amountInPaisa);
                $uplineGenealogy->increment('total_team_sales', $amountInPaisa);
            }
        });

        return $commissions;
    }

    /**
     * Calculate originator (agent/advisor) commission
     *
     * @param  User  $user  The recruited user
     * @param  int  $amountInPaisa  Purchase/subscription amount
     * @param  mixed  $commissionable  The model triggering commission
     */
    public function calculateOriginatorCommission(User $user, int $amountInPaisa, $commissionable): ?AffiliateCommission
    {
        if (! $user->originator) {
            return null;
        }

        $originator = $user->originator;

        // Get originator commission rate (could be based on originator level/tier)
        $rate = config('affiliate.originator_commission_rate', 5.0);
        $commissionAmount = (int) round($amountInPaisa * ($rate / 100));

        // Find originator's genealogy (if they have one)
        $originatorGenealogy = AffiliateGenealogy::forUser($originator->id);

        return AffiliateCommission::create([
            'user_id' => $originator->id,
            'genealogy_id' => $originatorGenealogy?->id,
            'from_user_id' => $user->id,
            'commissionable_type' => get_class($commissionable),
            'commissionable_id' => $commissionable->getKey(),
            'type' => CommissionTypeCast::SPONSOR_BONUS->value,
            'rate_percent' => $rate,
            'base_amount' => $amountInPaisa,
            'gross_amount' => $commissionAmount,
            'net_amount' => $commissionAmount,
            'status' => CommissionStatusCast::PENDING,
            'description' => 'Originator commission for recruitment',
        ]);
    }

    /**
     * Get upline users with their level numbers (optimized)
     *
     * @return array<int, User> [level => User]
     */
    public function getUplineWithLevels(int $userId, int $maxLevels = 4): array
    {
        $uplines = [];
        $currentUser = User::find($userId);

        if (! $currentUser) {
            return $uplines;
        }

        $level = 1;
        $parentId = $currentUser->parent_id;

        // Use optimized query to fetch all ancestors at once
        if ($parentId) {
            $ancestorIds = $this->getAncestorIds($currentUser, $maxLevels);

            // Fetch all ancestors in single query
            $ancestors = User::whereIn('id', $ancestorIds)
                ->get()
                ->keyBy('id');

            // Build upline array in correct order
            $currentParentId = $parentId;
            while ($currentParentId && $level <= $maxLevels) {
                if (isset($ancestors[$currentParentId])) {
                    $uplines[$level] = $ancestors[$currentParentId];
                    $currentParentId = $ancestors[$currentParentId]->parent_id;
                    $level++;
                } else {
                    break;
                }
            }
        }

        return $uplines;
    }

    /**
     * Get ancestor IDs using recursive CTE (optimized for databases that support it)
     *
     * @return array<int>
     */
    private function getAncestorIds(User $user, int $maxLevels): array
    {
        // For MySQL 8.0+ with recursive CTE support
        // Falls back to iterative approach if not available
        try {
            $result = DB::select('
                WITH RECURSIVE ancestors AS (
                    SELECT id, parent_id, 1 as level
                    FROM users
                    WHERE id = ?
                    UNION ALL
                    SELECT u.id, u.parent_id, a.level + 1
                    FROM users u
                    INNER JOIN ancestors a ON u.id = a.parent_id
                    WHERE a.level < ?
                )
                SELECT id FROM ancestors WHERE id != ?
            ', [$user->id, $maxLevels + 1, $user->id]);

            return array_column($result, 'id');
        } catch (\Exception $e) {
            // Fallback to iterative approach
            return $this->getAncestorIdsIterative($user, $maxLevels);
        }
    }

    /**
     * Get ancestor IDs iteratively (fallback)
     */
    private function getAncestorIdsIterative(User $user, int $maxLevels): array
    {
        $ancestorIds = [];
        $currentParentId = $user->parent_id;
        $level = 0;

        while ($currentParentId && $level < $maxLevels) {
            $ancestorIds[] = $currentParentId;
            $parent = User::select('id', 'parent_id')->find($currentParentId);
            $currentParentId = $parent?->parent_id;
            $level++;
        }

        return $ancestorIds;
    }

    /**
     * Increment counters for all uplines when new user joins
     */
    public function incrementUplineCounters(int $newUserId): void
    {
        $newMember = AffiliateGenealogy::forUser($newUserId);
        if (! $newMember) {
            return;
        }

        $uplines = $this->getUplineWithLevels($newUserId, $this->maxDepthLevels);

        foreach ($uplines as $level => $uplineUser) {
            $uplineGenealogy = AffiliateGenealogy::forUser($uplineUser->id);
            if (! $uplineGenealogy) {
                continue;
            }

            if ($level <= 4) {
                $levelField = "level_{$level}_count";
                $uplineGenealogy->increment($levelField);
                $uplineGenealogy->increment('total_team_count');

                if ($newMember->is_active) {
                    $uplineGenealogy->increment('active_team_count');
                }
            }

            if ($level === 1) {
                $uplineGenealogy->increment('direct_count');
                if ($newMember->is_active) {
                    $uplineGenealogy->increment('active_direct_count');
                }
            }
        }
    }

    /**
     * Get direct children count for a user (optimized)
     */
    public function getDirectChildrenCount(int $userId): int
    {
        return User::where('parent_id', $userId)->count();
    }

    /**
     * Get children counts for multiple users at once (optimized)
     *
     * @param  array<int>  $userIds
     * @return array<int, int> [user_id => count]
     */
    public function getChildrenCountsForUsers(array $userIds): array
    {
        return User::whereIn('parent_id', $userIds)
            ->selectRaw('parent_id, COUNT(*) as count')
            ->groupBy('parent_id')
            ->pluck('count', 'parent_id')
            ->toArray();
    }

    /**
     * Check if user can accept more children
     */
    public function canAcceptChildren(int $userId): bool
    {
        return $this->getDirectChildrenCount($userId) < $this->maxDirectChildren;
    }

    /**
     * Get team statistics for a user
     */
    public function getTeamStats(int $userId): array
    {
        $genealogy = AffiliateGenealogy::forUser($userId);

        if (! $genealogy) {
            return [
                'direct_count' => 0,
                'level_1_count' => 0,
                'level_2_count' => 0,
                'level_3_count' => 0,
                'level_4_count' => 0,
                'total_team_count' => 0,
                'active_team_count' => 0,
            ];
        }

        return [
            'direct_count' => $genealogy->direct_count,
            'level_1_count' => $genealogy->level_1_count,
            'level_2_count' => $genealogy->level_2_count,
            'level_3_count' => $genealogy->level_3_count,
            'level_4_count' => $genealogy->level_4_count,
            'total_team_count' => $genealogy->total_team_count,
            'active_team_count' => $genealogy->active_team_count,
        ];
    }
}
