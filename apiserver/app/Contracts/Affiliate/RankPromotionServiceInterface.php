<?php

declare(strict_types=1);

namespace App\Contracts\Affiliate;

use App\Models\Membership\Stage;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Contract for Rank Promotion Service
 *
 * Handles Affiliate rank/stage progression, eligibility checks, promotions,
 * and rank-based rewards. Supports both automatic and manual promotions.
 *
 * Key responsibilities:
 * - Check rank eligibility based on criteria
 * - Process automatic promotions
 * - Calculate rank-based bonuses
 * - Track rank history
 * - Handle rank demotions (if applicable)
 *
 * Scalability considerations for 1B+ users:
 * - Batch process rank checks during off-peak (queue jobs)
 * - Cache eligibility calculations per user (Redis with TTL)
 * - Use database transactions with row-level locks
 * - Event-driven: emit events for downstream processing
 * - Idempotent promotions (safe to retry)
 */
interface RankPromotionServiceInterface
{
    // ========================================
    // ELIGIBILITY CHECKS
    // ========================================

    /**
     * Check if user is eligible for next rank
     */
    public function isEligibleForNextRank(User $user): bool;

    /**
     * Check if user is eligible for a specific stage
     */
    public function isEligibleForStage(User $user, Stage $stage): bool;

    /**
     * Get eligibility status with detailed criteria breakdown
     *
     * @return array{
     *     eligible: bool,
     *     current_stage: array{id: int, name: string, rank: int}|null,
     *     next_stage: array{id: int, name: string, rank: int}|null,
     *     criteria: array<string, array{required: mixed, current: mixed, met: bool}>,
     *     missing_criteria: array<string>
     * }
     */
    public function getEligibilityStatus(User $user): array;

    /**
     * Get eligibility for a specific stage with criteria breakdown
     *
     * @return array{
     *     eligible: bool,
     *     criteria: array<string, array{required: mixed, current: mixed, met: bool}>
     * }
     */
    public function getStageEligibility(User $user, Stage $stage): array;

    // ========================================
    // PROMOTION OPERATIONS
    // ========================================

    /**
     * Promote user to next eligible rank
     *
     * @return array{
     *     promoted: bool,
     *     from_stage: array{id: int, name: string}|null,
     *     to_stage: array{id: int, name: string}|null,
     *     bonuses_awarded: array<int, array{type: string, amount: int}>,
     *     message: string
     * }
     */
    public function promoteToNextRank(User $user): array;

    /**
     * Promote user to a specific stage (admin/manual)
     *
     * @param  bool  $bypassEligibility  Skip eligibility check (admin override)
     * @return array{
     *     promoted: bool,
     *     from_stage: array{id: int, name: string}|null,
     *     to_stage: array{id: int, name: string}|null,
     *     bonuses_awarded: array<int, array{type: string, amount: int}>,
     *     message: string
     * }
     */
    public function promoteToStage(User $user, Stage $stage, bool $bypassEligibility = false): array;

    /**
     * Process automatic promotions for all eligible users
     *
     * Should be run as a scheduled job during off-peak hours.
     *
     * @param  int|null  $limit  Maximum users to process (for batching)
     * @return array{
     *     processed: int,
     *     promoted: int,
     *     failed: int,
     *     details: array<int, array{user_id: int, from: string|null, to: string|null, success: bool}>
     * }
     */
    public function processAutomaticPromotions(?int $limit = null): array;

    /**
     * Queue automatic promotion check for a user
     *
     * Dispatches job for async processing.
     */
    public function queuePromotionCheck(User $user): void;

    // ========================================
    // RANK INFORMATION
    // ========================================

    /**
     * Get user's current rank/stage
     */
    public function getCurrentStage(User $user): ?Stage;

    /**
     * Get user's current global rank number
     */
    public function getCurrentRank(User $user): int;

    /**
     * Get next achievable stage for user
     */
    public function getNextStage(User $user): ?Stage;

    /**
     * Get all stages with user's progress towards each
     *
     * @return Collection<int, array{
     *     stage: Stage,
     *     achieved: bool,
     *     progress_percent: float,
     *     criteria_status: array<string, array{required: mixed, current: mixed, met: bool}>
     * }>
     */
    public function getAllStagesWithProgress(User $user): Collection;

    // ========================================
    // RANK BONUSES
    // ========================================

    /**
     * Calculate rank achievement bonus for a stage
     */
    public function calculateRankBonus(Stage $stage): int;

    /**
     * Award rank achievement bonus to user
     *
     * @return array{
     *     awarded: bool,
     *     amount: int,
     *     commission_id: int|null,
     *     message: string
     * }
     */
    public function awardRankBonus(User $user, Stage $stage): array;

    /**
     * Get rank bonuses configuration
     *
     * @return array<int, array{stage_id: int, stage_name: string, bonus_amount: int}>
     */
    public function getRankBonusConfig(): array;

    // ========================================
    // RANK HISTORY
    // ========================================

    /**
     * Get user's rank history
     *
     * @return Collection<int, array{
     *     stage_id: int,
     *     stage_name: string,
     *     achieved_at: string,
     *     bonus_awarded: int|null
     * }>
     */
    public function getRankHistory(User $user): Collection;

    /**
     * Record rank achievement
     */
    public function recordRankAchievement(User $user, Stage $stage, ?int $bonusAmount = null): void;

    // ========================================
    // DEMOTION (Optional Feature)
    // ========================================

    /**
     * Check if demotions are enabled
     */
    public function isDemotionEnabled(): bool;

    /**
     * Check if user should be demoted based on maintenance criteria
     */
    public function shouldDemote(User $user): bool;

    /**
     * Process demotion for a user
     *
     * @return array{
     *     demoted: bool,
     *     from_stage: array{id: int, name: string}|null,
     *     to_stage: array{id: int, name: string}|null,
     *     reason: string
     * }
     */
    public function processDemotion(User $user): array;

    // ========================================
    // METRICS & CRITERIA HELPERS
    // ========================================

    /**
     * Get user metrics for rank calculation
     *
     * @return array{
     *     direct_referrals: int,
     *     active_direct_referrals: int,
     *     total_team_size: int,
     *     active_team_size: int,
     *     personal_volume: int,
     *     team_volume: int,
     *     total_commissions_earned: int,
     *     months_active: int
     * }
     */
    public function getUserMetrics(User $user): array;

    /**
     * Get criteria requirements for a stage
     *
     * @return array<string, mixed>
     */
    public function getStageCriteria(Stage $stage): array;

    // ========================================
    // BATCH OPERATIONS (For Scalability)
    // ========================================

    /**
     * Get users eligible for promotion (for batch processing)
     *
     * @param  int  $limit  Maximum users to return
     * @return Collection<int, User>
     */
    public function getEligibleForPromotion(int $limit = 1000): Collection;

    /**
     * Batch check promotions for multiple users
     *
     * @param  Collection<int, User>  $users
     * @return array<int, array{user_id: int, eligible: bool, next_stage_id: int|null}>
     */
    public function batchCheckEligibility(Collection $users): array;
}
