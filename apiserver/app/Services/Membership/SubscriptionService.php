<?php

declare(strict_types=1);

namespace App\Services\Membership;

use App\Events\Affiliate\SubscriptionActivated;
use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\User;
use App\Services\Affiliate\CommissionProcessorService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Subscription Service - Orchestrates membership subscription lifecycle
 *
 * Handles:
 * - New subscriptions (with sponsor tracking - who paid)
 * - Subscription activation + commission triggering
 * - Level progression checks
 * - Renewals and upgrades
 */
final class SubscriptionService
{
    public function __construct(
        private readonly CommissionProcessorService $commissionProcessor,
    ) {}

    /**
     * Create a new subscription for a user
     *
     * @param  User  $user  The subscribing user
     * @param  Stage  $stage  The membership stage
     * @param  User|null  $sponsor  Who paid for this subscription (nullable)
     */
    public function createSubscription(
        User $user,
        Stage $stage,
        ?User $sponsor = null,
    ): UserSubscription {
        // Get first level of the stage
        $firstLevel = $stage->getFirstLevel();

        if (! $firstLevel) {
            throw new \RuntimeException("Stage {$stage->name} has no levels configured");
        }

        return UserSubscription::create([
            'user_id' => $user->id,
            'stage_id' => $stage->id,
            'level_id' => $firstLevel->id,
            'status' => UserSubscription::STATUS_PENDING,
            'sponsor_type' => $sponsor ? User::class : null,
            'sponsor_id' => $sponsor?->id,
        ]);
    }

    /**
     * Activate subscription after payment and trigger commissions
     *
     * This is the main entry point for subscription activation flow:
     * 1. Activate subscription
     * 2. Create/update genealogy record
     * 3. Update upline counters
     * 4. Process commissions (sponsor bonus, level commissions, originator)
     */
    public function activateSubscription(
        UserSubscription $subscription,
        ?int $transactionId = null,
        bool $processCommissions = true,
    ): array {
        $results = [
            'subscription' => null,
            'genealogy' => null,
            'commissions' => collect(),
            'errors' => [],
        ];

        try {
            DB::transaction(function () use ($subscription, $transactionId, $processCommissions, &$results) {
                // 1. Activate the subscription
                $subscription->activate($transactionId);
                $subscription->refresh();
                $results['subscription'] = $subscription;

                $user = $subscription->user;

                // 2. Create or update genealogy record
                $genealogy = $this->ensureGenealogyRecord($user, $subscription);
                $results['genealogy'] = $genealogy;

                // 3. Update upline counters
                AffiliateGenealogy::incrementUplineCounters($user->id);

                // 4. Add sales to genealogy (personal + propagate to uplines)
                $genealogy->addSales($subscription->amount, $subscription->stage?->pv ?? 0);

                // 5. Process commissions
                if ($processCommissions) {
                    $results['commissions'] = $this->commissionProcessor->processAndPersist($subscription);
                }

                // 6. Dispatch event for listeners
                SubscriptionActivated::dispatch($subscription, $results['commissions']);
            });

            Log::channel('affiliate')->info('Subscription activated', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'stage' => $subscription->stage?->name,
                'commissions_count' => $results['commissions']->count(),
                'total_commission' => $results['commissions']->sum('gross_amount'),
            ]);

        } catch (\Throwable $e) {
            Log::channel('affiliate')->error('Subscription activation failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            $results['errors'][] = $e->getMessage();
            throw $e;
        }

        return $results;
    }

    /**
     * Ensure genealogy record exists for user
     */
    private function ensureGenealogyRecord(User $user, UserSubscription $subscription): AffiliateGenealogy
    {
        $genealogy = AffiliateGenealogy::forUser($user->id);

        if ($genealogy) {
            // Update existing genealogy with new stage/level
            $genealogy->updateStageLevel($subscription->stage_id, $subscription->current_level_id);

            return $genealogy;
        }

        // Create new genealogy record
        return AffiliateGenealogy::createForUser($user->id);
    }

    /**
     * Check and promote user to next level if qualified
     */
    public function checkAndPromoteLevel(User $user): ?Level
    {
        $subscription = UserSubscription::getActiveForUser($user->id);
        if (! $subscription) {
            return null;
        }

        $genealogy = AffiliateGenealogy::forUser($user->id);
        if (! $genealogy) {
            return null;
        }

        $stats = [
            'direct_count' => $genealogy->direct_count,
            'active_direct_count' => $genealogy->active_direct_count,
            'personal_sales' => $genealogy->personal_sales,
            'team_sales' => $genealogy->total_team_sales,
            'level_1_count' => $genealogy->level_1_count,
            'level_2_count' => $genealogy->level_2_count,
            'level_3_count' => $genealogy->level_3_count,
            'level_4_count' => $genealogy->level_4_count,
            'total_team_count' => $genealogy->total_team_count,
        ];

        $nextLevel = $subscription->getNextLevel();
        if (! $nextLevel) {
            return null; // Already at max level in stage
        }

        if ($nextLevel->checkQualification($stats)) {
            $subscription->promoteToLevel($nextLevel, $stats);
            $genealogy->updateStageLevel($subscription->stage_id, $nextLevel->id);

            Log::channel('affiliate')->info('User promoted to next level', [
                'user_id' => $user->id,
                'new_level' => $nextLevel->full_name,
                'stats' => $stats,
            ]);

            return $nextLevel;
        }

        return null;
    }

    /**
     * Get level progression status for a user
     */
    public function getLevelProgressionStatus(User $user): array
    {
        $subscription = UserSubscription::getActiveForUser($user->id);
        if (! $subscription) {
            return ['error' => 'No active subscription'];
        }

        $genealogy = AffiliateGenealogy::forUser($user->id);
        if (! $genealogy) {
            return ['error' => 'No genealogy record'];
        }

        $currentLevel = $subscription->currentLevel;
        $nextLevel = $subscription->getNextLevel();

        $stats = [
            'direct_count' => $genealogy->direct_count,
            'active_direct_count' => $genealogy->active_direct_count,
            'personal_sales' => $genealogy->personal_sales,
            'team_sales' => $genealogy->total_team_sales,
        ];

        return [
            'current_stage' => $subscription->stage?->name,
            'current_level' => $currentLevel?->full_name,
            'current_level_number' => $currentLevel?->level_number,
            'next_level' => $nextLevel?->full_name,
            'next_level_requirements' => $nextLevel?->getQualificationProgress($stats),
            'can_promote' => $nextLevel?->checkQualification($stats) ?? false,
            'stats' => $stats,
            'team_capacity' => [
                'level_1' => ['current' => $genealogy->level_1_count, 'max' => 5],
                'level_2' => ['current' => $genealogy->level_2_count, 'max' => 25],
                'level_3' => ['current' => $genealogy->level_3_count, 'max' => 125],
                'level_4' => ['current' => $genealogy->level_4_count, 'max' => 625],
                'total' => ['current' => $genealogy->total_team_count, 'max' => 780],
            ],
        ];
    }

    /**
     * Create subscription with sponsor tracking (who paid for it)
     *
     * Note: Sponsor (who paid) is different from:
     * - parent_id: Affiliate upline (for commissions)
     * - originator: Agent/advisor who recruited (tracked on User model)
     *
     * Use cases:
     * - Self-subscribe: sponsor = null (user paid themselves)
     * - Gift subscription: sponsor = User who paid
     */
    public function createSponsoredSubscription(
        User $user,
        Stage $stage,
        User $sponsor,
    ): UserSubscription {
        return $this->createSubscription($user, $stage, $sponsor);
    }

    /**
     * Upgrade user to next stage
     */
    public function upgradeToNextStage(User $user, ?int $transactionId = null): ?UserSubscription
    {
        $currentSubscription = UserSubscription::getActiveForUser($user->id);
        if (! $currentSubscription) {
            return null;
        }

        $nextStage = $currentSubscription->stage?->getNextStage();
        if (! $nextStage) {
            return null; // No upgrade available
        }

        // Mark current as upgraded
        $currentSubscription->markAsUpgraded();

        // Create new subscription for next stage
        $newSubscription = UserSubscription::create([
            'user_id' => $user->id,
            'stage_id' => $nextStage->id,
            'level_id' => $nextStage->getFirstLevel()?->id,
            'previous_subscription_id' => $currentSubscription->id,
            'status' => UserSubscription::STATUS_PENDING,
        ]);

        // If transaction provided, activate immediately
        if ($transactionId) {
            $this->activateSubscription($newSubscription, $transactionId);
        }

        return $newSubscription;
    }

    /**
     * Get all stages with pricing for display
     */
    public function getAvailableStages(): Collection
    {
        return Stage::active()
            ->ordered()
            ->with(['levels' => fn ($q) => $q->active()->byLevelNumber()])
            ->get();
    }

    /**
     * Simulate commission calculation for preview (no persistence)
     */
    public function simulateCommissions(User $user, Stage $stage): array
    {
        $subscription = new UserSubscription([
            'user_id' => $user->id,
            'stage_id' => $stage->id,
            'level_id' => $stage->getFirstLevel()?->id,
            'amount' => $stage->price,
        ]);

        // Use a mock ID for simulation
        $subscription->id = 0;

        return $this->commissionProcessor->simulate($subscription);
    }
}
