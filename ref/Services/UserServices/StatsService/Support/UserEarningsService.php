<?php

namespace App\Services\UserServices\StatsService\Support;

use App\Models\Enums\AuthStatusCast;
use App\Models\Enums\CommissionCast;
use App\Models\Lifecycle\Level;
use App\Models\User;

class UserEarningsService
{

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the total earnings for the user.
     *
     * @return float
     */
    public function getTotalEarnings(): float
    {
        return $this->getAffiliateEarnings() +
            $this->getProductSalesEarnings() +
            $this->getServiceSalesEarnings() +
            $this->getTeamBonus() +
            $this->getLevelBonus() +
            $this->getResidualIncome() +
            $this->getRewardEarnings() +
            $this->getPromotionalBonus() +
            $this->getLeadershipBonus() +
            $this->getMatchingBonus() +
            $this->getRankAdvancementBonus();
    }


    public function getApproxAvailableAffiliateEarning()
    {
        // Get all team members who are not subscribed
        $allTeamMembersNotSubscribed = $this->user->descendantsAndSelf()
            ->whereIn('status', AuthStatusCast::draftStatusTypes())
            ->count();

        // Get the first level and its associated stage
        $firstLevel = Level::firstRecord('id', fn($query) => $query->with(['stage']));
        if (!$firstLevel || !$firstLevel->stage) {
            return 0; // If no level or stage is found, return 0
        }

        // Get the user's current level
        $userLevel = $this->user->level()->first();
        if (!$userLevel || !$userLevel->stage) {
            return 0; // If no user level or stage is found, return 0
        }


        // Retrieve the joining bonus percentage and stage price
        $joiningBonusPercentage = $userLevel->joining_bonus; // Example: 10 (for 10%)
        $stagePrice = $userLevel->stage->price;
        // Calculate total commission
        $commissionPerMember = ($joiningBonusPercentage / 100) * $stagePrice;
        $totalApproxEarning = $allTeamMembersNotSubscribed * $commissionPerMember;

        return $totalApproxEarning;
    }


    /**
     * Get the affiliate earnings for the user.
     *
     * @return float
     */
    public function getAffiliateEarnings(): float
    {
        // Implement the logic to calculate affiliate earnings.
        return $this->user->affiliateEarnings()->whereIn('commission_type',[CommissionCast::SELF_JOINING,CommissionCast::TEAM_JOINING])->sum('commission_amount');
    }

    /**
     * Get the product sales earnings for the user.
     *
     * @return float
     */
    public function getProductSalesEarnings(): float
    {
        // Implement the logic to calculate product sales earnings.
        return $this->user->productSales()->sum('amount');
    }

    /**
     * Get the service sales earnings for the user.
     *
     * @return float
     */
    public function getServiceSalesEarnings(): float
    {
        // Implement the logic to calculate service sales earnings.
        return $this->user->serviceSales()->sum('amount');
    }

    /**
     * Get the team bonus for the user.
     *
     * @return float
     */
    public function getTeamBonus(): float
    {
        // Implement the logic to calculate team bonus.
        return $this->user->teamBonuses()->sum('amount');
    }

    /**
     * Get the level bonus for the user.
     *
     * @return float
     */
    public function getLevelBonus(): float
    {
        // Implement the logic to calculate level bonus.
        return $this->user->levelBonuses()->sum('amount');
    }

    /**
     * Get the residual income for the user.
     *
     * @return float
     */
    public function getResidualIncome(): float
    {
        // Implement the logic to calculate residual income.
        return $this->user->residualIncomes()->sum('amount');
    }

    /**
     * Get the reward earnings for the user.
     *
     * @return float
     */
    public function getRewardEarnings(): float
    {
        // Implement the logic to calculate reward earnings.
        return $this->user->rewardEarnings()->sum('amount');
    }

    /**
     * Get the promotional bonus for the user.
     *
     * @return float
     */
    public function getPromotionalBonus(): float
    {
        // Implement the logic to calculate promotional bonus.
        return $this->user->promotionalBonuses()->sum('amount');
    }

    /**
     * Get the leadership bonus for the user.
     *
     * @return float
     */
    public function getLeadershipBonus(): float
    {
        // Implement the logic to calculate leadership bonus.
        return $this->user->leadershipBonuses()->sum('amount');
    }

    /**
     * Get the matching bonus for the user.
     *
     * @return float
     */
    public function getMatchingBonus(): float
    {
        // Implement the logic to calculate matching bonus.
        return $this->user->matchingBonuses()->sum('amount');
    }

    /**
     * Get the rank advancement bonus for the user.
     *
     * @return float
     */
    public function getRankAdvancementBonus(): float
    {
        // Implement the logic to calculate rank advancement bonus.
        return $this->user->rankAdvancementBonuses()->sum('amount');
    }

}
