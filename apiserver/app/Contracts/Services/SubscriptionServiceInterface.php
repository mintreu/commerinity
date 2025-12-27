<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\User;
use Illuminate\Support\Collection;

interface SubscriptionServiceInterface
{
    /**
     * Create a new subscription for user
     */
    public function createSubscription(User $user, Stage $stage, ?User $sponsor = null): UserSubscription;

    /**
     * Create a sponsored subscription (gift)
     */
    public function createSponsoredSubscription(User $targetUser, Stage $stage, User $sponsor): UserSubscription;

    /**
     * Activate subscription after payment
     */
    public function activateSubscription(UserSubscription $subscription): UserSubscription;

    /**
     * Check and promote user to next level if eligible
     *
     * @return array{promoted: bool, new_level: ?string, message: string}
     */
    public function checkAndPromoteLevel(User $user): array;

    /**
     * Get level progression status for user
     *
     * @return array{
     *   current_level: ?string,
     *   next_level: ?string,
     *   progress_percent: int,
     *   requirements: array,
     *   met_requirements: array
     * }
     */
    public function getLevelProgressionStatus(User $user): array;

    /**
     * Upgrade user to next stage
     */
    public function upgradeToNextStage(User $user): ?UserSubscription;

    /**
     * Get available stages for subscription
     */
    public function getAvailableStages(): Collection;

    /**
     * Simulate commissions for a subscription
     *
     * @return array{
     *   sponsor_bonus: int,
     *   level_commissions: array,
     *   originator_commission: int,
     *   total: int
     * }
     */
    public function simulateCommissions(User $user, Stage $stage): array;
}
