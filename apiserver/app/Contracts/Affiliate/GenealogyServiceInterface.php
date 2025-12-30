<?php

declare(strict_types=1);

namespace App\Contracts\Affiliate;

use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Contract for Genealogy Service
 *
 * Handles genealogy record management, stage tracking, and auto-placement
 * within the Affiliate tree structure. Each user has one AffiliateGenealogy record
 * that tracks their position, stage progress, and parent relationships.
 *
 * Key responsibilities:
 * - Create/update genealogy records
 * - Auto-placement algorithm (spillover)
 * - Stage completion tracking
 * - Cache genealogy lookups for performance
 *
 * Scalability considerations for 1B+ users:
 * - Cache genealogy by user_id in Redis (TTL: 1 hour)
 * - Use read replicas for genealogy queries
 * - Batch create genealogies during user registration spikes
 * - Materialized paths for efficient ancestor/descendant queries
 * - Idempotent operations for safe retries
 */
interface GenealogyServiceInterface
{
    // ========================================
    // GENEALOGY CRUD
    // ========================================

    /**
     * Get or create genealogy for a user
     *
     * Should be cached to avoid repeated DB lookups.
     */
    public function getOrCreate(User $user): AffiliateGenealogy;

    /**
     * Get genealogy by user ID (cached)
     */
    public function getByUserId(int $userId): ?AffiliateGenealogy;

    /**
     * Create genealogy for new user
     */
    public function createForUser(User $user, ?User $parent = null, ?User $sponsor = null): AffiliateGenealogy;

    /**
     * Update genealogy record
     *
     * @param  array<string, mixed>  $data
     */
    public function update(AffiliateGenealogy $genealogy, array $data): AffiliateGenealogy;

    // ========================================
    // AUTO-PLACEMENT (Spillover)
    // ========================================

    /**
     * Find optimal placement position for new user
     *
     * Uses BFS to find first available slot in sponsor's downline.
     * Respects matrix width constraints.
     *
     * @return User The parent to place under
     */
    public function findPlacementPosition(User $sponsor): User;

    /**
     * Place user in tree with auto-placement
     *
     * Creates genealogy record and places user optimally.
     */
    public function placeWithSpillover(User $newUser, User $sponsor): AffiliateGenealogy;

    /**
     * Check if user can be placed under a specific parent
     */
    public function canPlaceUnder(User $parent): bool;

    // ========================================
    // STAGE TRACKING
    // ========================================

    /**
     * Update user's current stage
     */
    public function updateStage(User $user, int $stageId): void;

    /**
     * Record stage completion
     *
     * Called when user completes a matrix stage.
     */
    public function recordStageCompletion(User $user, int $stageId): void;

    /**
     * Get user's stage completion history
     *
     * @return Collection<int, array{stage_id: int, completed_at: string}>
     */
    public function getStageHistory(User $user): Collection;

    /**
     * Check if user has completed a stage
     */
    public function hasCompletedStage(User $user, int $stageId): bool;

    // ========================================
    // PARENT/SPONSOR RELATIONSHIPS
    // ========================================

    /**
     * Get genealogy parent (placement parent)
     */
    public function getParent(User $user): ?User;

    /**
     * Get sponsor (referrer)
     */
    public function getSponsor(User $user): ?User;

    /**
     * Update placement parent
     *
     * Used when reassigning children on deletion.
     */
    public function updateParent(User $user, ?User $newParent): void;

    /**
     * Check if user1 is an ancestor of user2
     */
    public function isAncestor(User $potentialAncestor, User $user): bool;

    // ========================================
    // GENEALOGY QUERIES (Optimized)
    // ========================================

    /**
     * Get ancestors up to N levels (cached)
     *
     * @return Collection<int, AffiliateGenealogy>
     */
    public function getAncestors(User $user, int $levels = 10): Collection;

    /**
     * Get descendants up to N levels (for commission calc)
     *
     * @return Collection<int, AffiliateGenealogy>
     */
    public function getDescendants(User $user, int $levels = 4): Collection;

    /**
     * Get direct children genealogies
     *
     * @return Collection<int, AffiliateGenealogy>
     */
    public function getDirectChildren(User $user): Collection;

    /**
     * Get count of descendants at each level
     *
     * @return array<int, int> Level => Count
     */
    public function getDescendantCountByLevel(User $user, int $maxLevel = 4): array;

    // ========================================
    // BATCH OPERATIONS (For Scalability)
    // ========================================

    /**
     * Preload genealogies for multiple users (cache warming)
     *
     * @param  array<int>  $userIds
     * @return Collection<int, AffiliateGenealogy>
     */
    public function preloadByUserIds(array $userIds): Collection;

    /**
     * Batch create genealogies for new users
     *
     * @param  Collection<int, array{user: User, parent: User|null, sponsor: User|null}>  $placements
     * @return Collection<int, AffiliateGenealogy>
     */
    public function batchCreate(Collection $placements): Collection;

    /**
     * Clear genealogy cache for a user
     */
    public function clearCache(int $userId): void;

    /**
     * Clear all genealogy caches (maintenance)
     */
    public function clearAllCaches(): void;

    // ========================================
    // STATISTICS
    // ========================================

    /**
     * Get genealogy statistics for a user
     *
     * @return array{
     *     total_descendants: int,
     *     direct_children: int,
     *     by_level: array<int, int>,
     *     active_descendants: int,
     *     current_stage: int,
     *     stages_completed: int
     * }
     */
    public function getStatistics(User $user): array;
}
