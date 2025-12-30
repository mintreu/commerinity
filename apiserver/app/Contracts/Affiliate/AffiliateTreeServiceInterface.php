<?php

declare(strict_types=1);

namespace App\Contracts\Affiliate;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Contract for Affiliate Tree Service
 *
 * Manages the Affiliate tree structure including node placement, tree navigation,
 * child reassignment, and tree statistics. Handles matrix constraints
 * (e.g., 5x4 matrix with max 5 direct children).
 *
 * Key responsibilities:
 * - Tree navigation (upline, downline, ancestors, descendants)
 * - Child placement with matrix constraints
 * - Child reassignment on user deletion
 * - Tree statistics and depth calculation
 *
 * Scalability considerations for 1B+ users:
 * - Use materialized path or nested set for efficient queries
 * - Cache frequently accessed tree paths in Redis
 * - Batch tree updates during off-peak hours
 * - Use database indexes on parent_id, path columns
 * - Consider sharding by tree root for very large networks
 */
interface AffiliateTreeServiceInterface
{
    // ========================================
    // TREE NAVIGATION
    // ========================================

    /**
     * Get complete downline (all descendants) of a user
     *
     * For large trees, consider using pagination or limiting depth.
     *
     * @return Collection<int, User>
     */
    public function getDownline(User $user): Collection;

    /**
     * Get complete upline (all ancestors) of a user
     *
     * @return Collection<int, User>
     */
    public function getUpline(User $user): Collection;

    /**
     * Get downline limited to specific depth
     *
     * @param  int  $maxDepth  Maximum levels to traverse (e.g., 4 for 5x4 matrix)
     * @return Collection<int, User>
     */
    public function getDownlineToDepth(User $user, int $maxDepth): Collection;

    /**
     * Get upline limited to specific levels
     *
     * @param  int  $maxLevels  Maximum levels to traverse up
     * @return Collection<int, User>
     */
    public function getUplineToLevel(User $user, int $maxLevels): Collection;

    /**
     * Get users at a specific level in user's downline
     *
     * @param  int  $level  Level number (1 = direct children, 2 = grandchildren, etc.)
     * @return Collection<int, User>
     */
    public function getUsersAtLevel(User $user, int $level): Collection;

    // ========================================
    // SLOT & PLACEMENT
    // ========================================

    /**
     * Check if user has available slots for direct children
     */
    public function hasAvailableSlots(User $user): bool;

    /**
     * Get number of available slots for a user
     */
    public function getAvailableSlots(User $user): int;

    /**
     * Check if user can accept new children
     */
    public function canAcceptChildren(User $user): bool;

    /**
     * Find first available parent in a user's downline
     *
     * Uses breadth-first search to find nearest user with available slots.
     * Used for auto-placement in matrix systems.
     */
    public function findFirstAvailableParent(User $sponsor): ?User;

    /**
     * Find available ancestor for child reassignment
     *
     * Traverses up from starting user to find nearest ancestor with slots.
     */
    public function findAvailableAncestor(User $startingUser): ?User;

    /**
     * Place a new user in the tree under a sponsor
     *
     * Handles auto-placement if sponsor's direct slots are full.
     *
     * @return User The assigned parent
     */
    public function placeUser(User $newUser, User $sponsor): User;

    // ========================================
    // CHILD REASSIGNMENT
    // ========================================

    /**
     * Reassign children when a user is deleted
     *
     * Finds appropriate new parents for orphaned children.
     *
     * @return array{reassigned: int, details: array<int, array{child_id: int, child_uuid: string, new_parent_id: int|null, new_parent_uuid: string|null}>}
     */
    public function reassignChildrenOnDeletion(User $deletedUser): array;

    // ========================================
    // DIRECT CHILDREN
    // ========================================

    /**
     * Get direct children count for a user
     */
    public function getDirectChildrenCount(User $user): int;

    /**
     * Get direct children collection
     *
     * @return Collection<int, User>
     */
    public function getDirectChildren(User $user): Collection;

    // ========================================
    // TREE STATISTICS
    // ========================================

    /**
     * Get Affiliate tree statistics for a user
     *
     * @return array{
     *     direct_children: int,
     *     max_direct_children: int,
     *     available_slots: int,
     *     total_downline: int,
     *     tree_depth: int
     * }
     */
    public function getTreeStats(User $user): array;

    /**
     * Get detailed tree statistics with level breakdown
     *
     * @return array{
     *     direct_children: int,
     *     max_direct_children: int,
     *     available_slots: int,
     *     total_downline: int,
     *     tree_depth: int,
     *     by_level: array<int, int>,
     *     active_in_tree: int
     * }
     */
    public function getDetailedTreeStats(User $user): array;

    /**
     * Calculate the depth of user's downline tree
     */
    public function calculateTreeDepth(User $user): int;

    // ========================================
    // TEAM METRICS (For Commission Calculations)
    // ========================================

    /**
     * Get total team size (all descendants)
     */
    public function getTeamSize(User $user): int;

    /**
     * Get active team members count
     */
    public function getActiveTeamCount(User $user): int;

    /**
     * Get team members with active subscriptions
     *
     * @return Collection<int, User>
     */
    public function getActiveTeamMembers(User $user): Collection;

    /**
     * Get count of users by level
     *
     * @return array<int, int> Level => Count mapping
     */
    public function getCountByLevel(User $user, int $maxLevel = 4): array;

    // ========================================
    // MATRIX CONFIGURATION
    // ========================================

    /**
     * Get maximum allowed direct children (matrix width)
     */
    public function getMaxDirectChildren(): int;

    /**
     * Get maximum tree depth (matrix depth)
     */
    public function getMaxTreeDepth(): int;

    /**
     * Get matrix configuration
     *
     * @return array{width: int, depth: int, max_per_stage: int}
     */
    public function getMatrixConfig(): array;
}
