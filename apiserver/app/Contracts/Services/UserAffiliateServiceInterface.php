<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Affiliate\AffiliateCommission;
use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\User;
use Illuminate\Support\Collection;

interface UserAffiliateServiceInterface
{
    /**
     * Find available slot for new user using BFS
     *
     * @return array{parent_id: int, position: int}|null
     */
    public function findAvailableSlot(User $targetUser): ?array;

    /**
     * Place new user in Affiliate tree
     */
    public function placeUser(User $newUser, ?User $sponsor = null, ?User $originator = null): AffiliateGenealogy;

    /**
     * Calculate and distribute commissions for a purchase/subscription
     *
     * @param  mixed  $commissionable  The model triggering commission
     * @return Collection<int, AffiliateCommission>
     */
    public function distributeCommissions(int $userId, int $amountInPaisa, mixed $commissionable): Collection;

    /**
     * Calculate originator (agent/advisor) commission
     *
     * @param  mixed  $commissionable  The model triggering commission
     */
    public function calculateOriginatorCommission(User $user, int $amountInPaisa, mixed $commissionable): ?AffiliateCommission;

    /**
     * Get upline users with their level numbers
     *
     * @return array<int, User>
     */
    public function getUplineWithLevels(int $userId, int $maxLevels = 4): array;

    /**
     * Increment counters for all uplines when new user joins
     */
    public function incrementUplineCounters(int $newUserId): void;

    /**
     * Get direct children count for a user
     */
    public function getDirectChildrenCount(int $userId): int;

    /**
     * Get children counts for multiple users at once
     *
     * @param  array<int>  $userIds
     * @return array<int, int>
     */
    public function getChildrenCountsForUsers(array $userIds): array;

    /**
     * Check if user can accept more children
     */
    public function canAcceptChildren(int $userId): bool;

    /**
     * Get team statistics for a user
     *
     * @return array<string, int>
     */
    public function getTeamStats(int $userId): array;
}
