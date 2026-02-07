<?php

declare(strict_types=1);

namespace App\Services\Trends;

use App\Casts\UserTypeCast;
use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\User;
use Flowframe\Trend\Trend;

/**
 * TeamTrendService - Team/Downline growth and activity trends
 *
 * Charts:
 * - Direct referral growth
 * - Team size over time
 * - Active vs inactive team members
 * - Originated users (for Advisors)
 */
final class TeamTrendService extends BaseTrendService
{
    /**
     * Get direct referral growth trend
     */
    public function getDirectReferralTrend(
        int $userId,
        string $period = 'year',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $query = User::query()
            ->where('parent_id', $userId);

        $trend = Trend::query($query)
            ->between(start: $dates['start'], end: $dates['end']);

        $data = match ($interval) {
            'day' => $trend->perDay()->count(),
            'week' => $trend->perDay()->count(),
            'month' => $trend->perMonth()->count(),
            'year' => $trend->perYear()->count(),
            default => $trend->perMonth()->count(),
        };

        $chartData = $this->formatForChart(
            $data,
            'New Referrals',
            '#6366F1',
            '#4F46E5'
        );

        // Current totals
        $totalDirect = User::where('parent_id', $userId)->count();
        $activeDirect = User::where('parent_id', $userId)
            ->where('type', '!=', UserTypeCast::REGULAR)
            ->count();

        $summary = [
            'new_referrals' => (int) $data->sum('aggregate'),
            'total_direct' => $totalDirect,
            'active_direct' => $activeDirect,
            'inactive_direct' => $totalDirect - $activeDirect,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get active vs inactive team members trend
     */
    public function getActiveInactiveTrend(
        int $userId,
        string $period = 'year',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        // Active = subscribed members (not regular)
        $activeQuery = User::query()
            ->where('parent_id', $userId)
            ->where('type', '!=', UserTypeCast::REGULAR);

        $inactiveQuery = User::query()
            ->where('parent_id', $userId)
            ->where('type', UserTypeCast::REGULAR);

        $activeTrend = Trend::query($activeQuery)
            ->between(start: $dates['start'], end: $dates['end']);

        $inactiveTrend = Trend::query($inactiveQuery)
            ->between(start: $dates['start'], end: $dates['end']);

        $activeData = match ($interval) {
            'day' => $activeTrend->perDay()->count(),
            'month' => $activeTrend->perMonth()->count(),
            'year' => $activeTrend->perYear()->count(),
            default => $activeTrend->perMonth()->count(),
        };

        $inactiveData = match ($interval) {
            'day' => $inactiveTrend->perDay()->count(),
            'month' => $inactiveTrend->perMonth()->count(),
            'year' => $inactiveTrend->perYear()->count(),
            default => $inactiveTrend->perMonth()->count(),
        };

        $chartData = $this->formatMultipleForChart(
            [
                'Active' => $activeData,
                'Inactive' => $inactiveData,
            ],
            [
                'Active' => ['bg' => '#10B981', 'border' => '#059669'],
                'Inactive' => ['bg' => '#6B7280', 'border' => '#4B5563'],
            ]
        );

        $summary = [
            'new_active' => (int) $activeData->sum('aggregate'),
            'new_inactive' => (int) $inactiveData->sum('aggregate'),
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get team distribution by user type
     */
    public function getTeamByType(
        int $userId,
        string $period = 'month',
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);

        // Get all direct referrals within period
        $query = User::query()
            ->where('parent_id', $userId)
            ->whereBetween('created_at', [$dates['start'], $dates['end']]);

        $types = UserTypeCast::cases();
        $data = [];
        $colors = $this->getUserTypeColors();

        foreach ($types as $type) {
            $count = (clone $query)
                ->where('type', $type)
                ->count();

            if ($count > 0) {
                $data[$type->value] = [
                    'label' => $type->getLabel(),
                    'count' => $count,
                    'color' => $colors[$type->value] ?? '#6B7280',
                ];
            }
        }

        $labels = array_column($data, 'label');
        $counts = array_column($data, 'count');
        $bgColors = array_column($data, 'color');

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $counts,
                    'backgroundColor' => $bgColors,
                ],
            ],
        ];

        $summary = [
            'total' => array_sum($counts),
            'breakdown' => $data,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period);
    }

    /**
     * Get originated users trend (for Advisors)
     */
    public function getOriginatedUsersTrend(
        int $originatorId,
        string $period = 'year',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $query = User::query()
            ->where('originator_id', $originatorId)
            ->where('originator_type', User::class);

        $trend = Trend::query($query)
            ->between(start: $dates['start'], end: $dates['end']);

        $data = match ($interval) {
            'day' => $trend->perDay()->count(),
            'month' => $trend->perMonth()->count(),
            'year' => $trend->perYear()->count(),
            default => $trend->perMonth()->count(),
        };

        $chartData = $this->formatForChart(
            $data,
            'Originated Users',
            '#8B5CF6',
            '#7C3AED'
        );

        // Get breakdown by type
        $totalOriginated = User::where('originator_id', $originatorId)
            ->where('originator_type', User::class)
            ->count();

        $activeOriginated = User::where('originator_id', $originatorId)
            ->where('originator_type', User::class)
            ->where('type', '!=', UserTypeCast::REGULAR)
            ->count();

        $summary = [
            'new_originated' => (int) $data->sum('aggregate'),
            'total_originated' => $totalOriginated,
            'active_originated' => $activeOriginated,
            'conversion_rate' => $totalOriginated > 0
                ? round(($activeOriginated / $totalOriginated) * 100, 2)
                : 0,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get team performance summary
     */
    public function getTeamPerformance(int $userId): array
    {
        // Direct referrals
        $directCount = User::where('parent_id', $userId)->count();
        $directActive = User::where('parent_id', $userId)
            ->where('type', '!=', UserTypeCast::REGULAR)
            ->count();

        // This month's new referrals
        $thisMonthReferrals = User::where('parent_id', $userId)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        // Last month's referrals for comparison
        $lastMonthReferrals = User::where('parent_id', $userId)
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();

        // Calculate growth rate
        $growthRate = $this->calculatePercentageChange($thisMonthReferrals, $lastMonthReferrals);

        return [
            'success' => true,
            'data' => [
                'direct_referrals' => $directCount,
                'active_referrals' => $directActive,
                'inactive_referrals' => $directCount - $directActive,
                'activation_rate' => $directCount > 0
                    ? round(($directActive / $directCount) * 100, 2)
                    : 0,
                'this_month' => $thisMonthReferrals,
                'last_month' => $lastMonthReferrals,
                'growth_rate' => $growthRate,
            ],
            'meta' => [
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Get team summary for dashboard
     * Used by dashboardSummary API endpoint
     */
    public function getTeamSummary(int $userId): array
    {
        // Direct referrals count
        $directCount = User::where('parent_id', $userId)->count();

        // Active members (Member, Promoter, Advisor, Mentor)
        $activeCount = User::where('parent_id', $userId)
            ->whereIn('type', [
                UserTypeCast::MEMBER,
                UserTypeCast::PROMOTER,
                UserTypeCast::ADVISOR,
                UserTypeCast::MENTOR,
            ])
            ->count();

        // Total team size (all descendants)
        $descendants = User::whereHas('ancestors', function ($query) use ($userId) {
            $query->where('id', $userId);
        })->count();

        // Level distribution from genealogy if available
        $levels = [
            'level_1' => $directCount,
            'level_2' => 0,
            'level_3' => 0,
            'level_4' => 0,
        ];

        // Try to get more detailed info from genealogy table
        $genealogy = AffiliateGenealogy::forUser($userId);
        if ($genealogy) {
            $levels = [
                'level_1' => $genealogy->level_1_count ?? $directCount,
                'level_2' => $genealogy->level_2_count ?? 0,
                'level_3' => $genealogy->level_3_count ?? 0,
                'level_4' => $genealogy->level_4_count ?? 0,
            ];
        }

        return [
            'success' => true,
            'data' => [
                'total_members' => $descendants + 1, // Including self
                'active_members' => $activeCount,
                'direct_referrals' => $directCount,
                'levels' => $levels,
            ],
            'meta' => [
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Get user type colors
     */
    private function getUserTypeColors(): array
    {
        return [
            UserTypeCast::REGULAR->value => '#6B7280',
            UserTypeCast::MEMBER->value => '#10B981',
            UserTypeCast::PROMOTER->value => '#3B82F6',
            UserTypeCast::ADVISOR->value => '#8B5CF6',
            UserTypeCast::MENTOR->value => '#F59E0B',
        ];
    }
}
