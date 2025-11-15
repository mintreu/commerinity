<?php

namespace App\Services\UserServices\StatsService\Support;

use App\Models\Enums\AuthStatusCast;
use App\Models\Enums\CommissionCast;
use App\Models\User;
use App\Services\MoneyService\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class UserReferralStatService
{
    protected Model|User $record;
    protected array $data;

    /**
     * Initialize the referral stats service.
     */
    public function __construct(Model|User $record)
    {
        $this->record = $record;
        $this->loadRelations();

        $this->data = $this->getCachedReport();
    }

    public static function make(Model|User $record): static
    {
        return new static($record);
    }

    public function getMeta(): array
    {
        return $this->data;
    }

    /**
     * Load required relationships.
     */
    private function loadRelations(): void
    {
        $this->record->loadMissing([
            'level',
            'descendants' => fn($query) => $query->whereIn('status',[AuthStatusCast::SUBSCRIBED,AuthStatusCast::ACTIVE,AuthStatusCast::TRIAL]),

            'affiliateEarnings' => fn($query) => $query->whereIn('commission_type', [
                CommissionCast::SELF_JOINING,
                CommissionCast::TEAM_JOINING
            ])
        ]);




    }

    /**
     * Retrieve cached report data.
     */
    private function getCachedReport(): array
    {
        $cacheKey = "user_referral_stats_{$this->record->id}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->getPreparedReport();
        });
    }

    /**
     * Generate referral stats report.
     */
    private function getPreparedReport(): array
    {

        $allTimeEarning = new Money($this->getTotalAffiliateEarnings());
        $allTimePaid = new Money($this->record->affiliateEarnings->where('is_paid',true)->sum('commission_amount'));
        $pendingEarning = $allTimeEarning->subOnce($allTimePaid);


        return [
            'totals' => [
                'children' => 0, // Placeholder, update if needed
                'descendents' => $this->record->descendants->count(),
                'earning' => Money::format($this->getTotalAffiliateEarnings()),
            ],
            'subscribed' => [
                'children' => 0, // Placeholder, update if needed
                'descendents' => $this->record->descendants->where('status', AuthStatusCast::SUBSCRIBED)->count(),
                'earning' => Money::format($this->getSubscribedEarnings()),
            ],
            'breakdown' => [
                'current_month' => [
                    'joining' => $this->getJoiningCount(now()->startOfMonth(), now()->endOfMonth()),
                    'commission_earned' => Money::format($this->getCommissionEarned(now()->startOfMonth(), now()->endOfMonth())),
                ],
                'last_month' => [
                    'joining' => $this->getJoiningCount(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()),
                    'commission_earned' => Money::format($this->getCommissionEarned(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth())),
                ],
            ],
            'earnings' => [
                'direct_referral_income' => Money::format($this->getDirectReferralIncome()),
                'team_referral_income' => Money::format($this->getTeamReferralIncome()),
                'total_referral_income' => Money::make($this->getDirectReferralIncome())->add($this->getTeamReferralIncome())->formatted(),
                'last_7_days' => Money::format($this->getEarningsLastDays(7)),
                'current_month' => Money::format($this->getEarningsForPeriod(now()->startOfMonth(), now()->endOfMonth())),
                'last_month' => Money::format($this->getEarningsForPeriod(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth())),
                'all_time' => Money::format($this->getTotalAffiliateEarnings()),
                'all_time_paid' => $allTimePaid->formatted(),
                'all_time_available' => $pendingEarning->formatted()
            ],
        ];
    }

    /**
     * Get total affiliate earnings.
     */
    protected function getTotalAffiliateEarnings(): float
    {
        return $this->record->affiliateEarnings->sum('commission_amount');
    }

    /**
     * Get earnings from subscribed referrals.
     */
    protected function getSubscribedEarnings(): float
    {
        return $this->record->affiliateEarnings
            ->whereIn('commission_type', [CommissionCast::SELF_JOINING, CommissionCast::TEAM_JOINING])
            ->sum('commission_amount');
    }

    /**
     * Get the count of new joinings in a given period.
     */
    protected function getJoiningCount($start, $end): int
    {
        return $this->record->descendants()
            ->where('status',AuthStatusCast::SUBSCRIBED)
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    /**
     * Get total commission earned within a given period.
     */
    protected function getCommissionEarned($start, $end): float
    {
        return $this->record->affiliateEarnings()
            ->whereBetween('created_at', [$start, $end])
            ->sum('commission_amount');
    }

    /**
     * Get direct referral income.
     */
    protected function getDirectReferralIncome(): float
    {
        return $this->record->affiliateEarnings()
            ->where('commission_type', CommissionCast::SELF_JOINING)
            ->sum('commission_amount');
    }

    /**
     * Get team referral income.
     */
    protected function getTeamReferralIncome(): float
    {
        return $this->record->affiliateEarnings()
            ->where('commission_type', CommissionCast::TEAM_JOINING)
            ->sum('commission_amount');
    }

    /**
     * Get earnings for the last X days.
     */
    protected function getEarningsLastDays(int $days): float
    {
        return $this->record->affiliateEarnings()
            ->where('created_at', '>=', now()->subDays($days))
            ->sum('commission_amount');
    }

    /**
     * Get earnings for a custom period.
     */
    protected function getEarningsForPeriod($start, $end): float
    {
        return $this->record->affiliateEarnings()
            ->whereBetween('created_at', [$start, $end])
            ->sum('commission_amount');
    }
}
