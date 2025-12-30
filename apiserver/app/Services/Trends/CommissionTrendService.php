<?php

declare(strict_types=1);

namespace App\Services\Trends;

use App\Casts\CommissionStatusCast;
use App\Casts\CommissionTypeCast;
use App\Models\Affiliate\AffiliateCommission;
use Flowframe\Trend\Trend;

/**
 * CommissionTrendService - Affiliate Commission trends and analytics
 *
 * Charts:
 * - Earnings over time
 * - By commission type
 * - Pending vs Paid
 * - Platform-wide commission stats (Admin)
 */
final class CommissionTrendService extends BaseTrendService
{
    /**
     * Get user's earnings trend over time
     */
    public function getEarningsTrend(
        int $userId,
        string $period = 'year',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $query = AffiliateCommission::query()
            ->where('user_id', $userId)
            ->where('status', CommissionStatusCast::PAID);

        $trend = Trend::query($query)
            ->dateColumn('commission_date')
            ->between(start: $dates['start'], end: $dates['end']);

        $data = match ($interval) {
            'day' => $trend->perDay()->sum('net_amount'),
            'week' => $trend->perDay()->sum('net_amount'),
            'month' => $trend->perMonth()->sum('net_amount'),
            'year' => $trend->perYear()->sum('net_amount'),
            default => $trend->perMonth()->sum('net_amount'),
        };

        if ($inRupees) {
            $data = $this->convertCollectionToRupees($data);
        }

        $chartData = $this->formatForChart(
            $data,
            'Earnings',
            '#10B981',
            '#059669'
        );

        $totalEarnings = $data->sum('aggregate');
        $averageEarnings = $data->count() > 0 ? round($totalEarnings / $data->count(), 2) : 0;

        $summary = [
            'total_earnings' => $totalEarnings,
            'average_monthly' => $averageEarnings,
            'highest_month' => $data->max('aggregate') ?? 0,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get earnings breakdown by commission type
     */
    public function getEarningsByType(
        int $userId,
        string $period = 'year',
        ?string $startDate = null,
        ?string $endDate = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);

        $query = AffiliateCommission::query()
            ->where('user_id', $userId)
            ->where('status', CommissionStatusCast::PAID)
            ->whereBetween('commission_date', [$dates['start'], $dates['end']]);

        $types = CommissionTypeCast::values();
        $labels = CommissionTypeCast::labels();
        $data = [];
        $colors = $this->getCommissionTypeColors();

        foreach ($types as $type) {
            // Skip reversal type for earnings display
            if ($type === CommissionTypeCast::REVERSAL) {
                continue;
            }

            $amount = (int) (clone $query)
                ->where('type', $type)
                ->sum('net_amount');

            $count = (clone $query)
                ->where('type', $type)
                ->count();

            if ($amount > 0) {
                $data[$type] = [
                    'label' => $labels[$type] ?? $type,
                    'amount' => $inRupees ? $this->paisaToRupees($amount) : $amount,
                    'count' => $count,
                    'color' => $colors[$type] ?? '#6B7280',
                ];
            }
        }

        $labels = array_column($data, 'label');
        $amounts = array_column($data, 'amount');
        $bgColors = array_column($data, 'color');

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $amounts,
                    'backgroundColor' => $bgColors,
                ],
            ],
        ];

        $summary = [
            'total_earnings' => array_sum($amounts),
            'breakdown' => $data,
            'top_source' => ! empty($data) ? array_keys($data, max($data))[0] ?? null : null,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period);
    }

    /**
     * Get pending vs paid commission trend
     */
    public function getPendingVsPaidTrend(
        int $userId,
        string $period = 'year',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $baseQuery = fn () => AffiliateCommission::query()
            ->where('user_id', $userId)
            ->where('type', '!=', CommissionTypeCast::REVERSAL);

        // Paid commissions
        $paidTrend = Trend::query($baseQuery()->where('status', CommissionStatusCast::PAID))
            ->dateColumn('commission_date')
            ->between(start: $dates['start'], end: $dates['end']);

        // Pending commissions (pending + approved)
        $pendingTrend = Trend::query(
            $baseQuery()->whereIn('status', [
                CommissionStatusCast::PENDING,
                CommissionStatusCast::APPROVED,
            ])
        )
            ->dateColumn('commission_date')
            ->between(start: $dates['start'], end: $dates['end']);

        $paidData = match ($interval) {
            'day' => $paidTrend->perDay()->sum('net_amount'),
            'month' => $paidTrend->perMonth()->sum('net_amount'),
            'year' => $paidTrend->perYear()->sum('net_amount'),
            default => $paidTrend->perMonth()->sum('net_amount'),
        };

        $pendingData = match ($interval) {
            'day' => $pendingTrend->perDay()->sum('net_amount'),
            'month' => $pendingTrend->perMonth()->sum('net_amount'),
            'year' => $pendingTrend->perYear()->sum('net_amount'),
            default => $pendingTrend->perMonth()->sum('net_amount'),
        };

        if ($inRupees) {
            $paidData = $this->convertCollectionToRupees($paidData);
            $pendingData = $this->convertCollectionToRupees($pendingData);
        }

        $chartData = $this->formatMultipleForChart(
            [
                'Paid' => $paidData,
                'Pending' => $pendingData,
            ],
            [
                'Paid' => ['bg' => '#10B981', 'border' => '#059669'],
                'Pending' => ['bg' => '#F59E0B', 'border' => '#D97706'],
            ]
        );

        $summary = [
            'total_paid' => $paidData->sum('aggregate'),
            'total_pending' => $pendingData->sum('aggregate'),
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get commission status distribution
     */
    public function getStatusDistribution(
        int $userId,
        string $period = 'month',
        ?string $startDate = null,
        ?string $endDate = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);

        $query = AffiliateCommission::query()
            ->where('user_id', $userId)
            ->where('type', '!=', CommissionTypeCast::REVERSAL)
            ->whereBetween('commission_date', [$dates['start'], $dates['end']]);

        $statuses = CommissionStatusCast::values();
        $statusLabels = CommissionStatusCast::labels();
        $data = [];
        $colors = $this->getStatusColors();

        foreach ($statuses as $status) {
            $amount = (int) (clone $query)
                ->where('status', $status)
                ->sum('net_amount');

            $count = (clone $query)
                ->where('status', $status)
                ->count();

            if ($amount > 0 || $count > 0) {
                $data[$status] = [
                    'label' => $statusLabels[$status] ?? $status,
                    'amount' => $inRupees ? $this->paisaToRupees($amount) : $amount,
                    'count' => $count,
                    'color' => $colors[$status] ?? '#6B7280',
                ];
            }
        }

        $labels = array_column($data, 'label');
        $amounts = array_column($data, 'amount');
        $bgColors = array_column($data, 'color');

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $amounts,
                    'backgroundColor' => $bgColors,
                ],
            ],
        ];

        $summary = [
            'total_amount' => array_sum($amounts),
            'breakdown' => $data,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period);
    }

    /**
     * Get platform-wide commission stats (Admin)
     */
    public function getPlatformCommissionTrend(
        string $period = 'year',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $query = AffiliateCommission::query()
            ->where('status', CommissionStatusCast::PAID)
            ->where('type', '!=', CommissionTypeCast::REVERSAL);

        $trend = Trend::query($query)
            ->dateColumn('commission_date')
            ->between(start: $dates['start'], end: $dates['end']);

        $commissionData = match ($interval) {
            'day' => $trend->perDay()->sum('net_amount'),
            'month' => $trend->perMonth()->sum('net_amount'),
            'year' => $trend->perYear()->sum('net_amount'),
            default => $trend->perMonth()->sum('net_amount'),
        };

        // TDS collected
        $tdsTrend = Trend::query(
            AffiliateCommission::query()
                ->where('status', CommissionStatusCast::PAID)
                ->where('tds_amount', '>', 0)
        )
            ->dateColumn('commission_date')
            ->between(start: $dates['start'], end: $dates['end']);

        $tdsData = match ($interval) {
            'day' => $tdsTrend->perDay()->sum('tds_amount'),
            'month' => $tdsTrend->perMonth()->sum('tds_amount'),
            'year' => $tdsTrend->perYear()->sum('tds_amount'),
            default => $tdsTrend->perMonth()->sum('tds_amount'),
        };

        if ($inRupees) {
            $commissionData = $this->convertCollectionToRupees($commissionData);
            $tdsData = $this->convertCollectionToRupees($tdsData);
        }

        $chartData = $this->formatMultipleForChart(
            [
                'Commissions Paid' => $commissionData,
                'TDS Collected' => $tdsData,
            ],
            [
                'Commissions Paid' => ['bg' => '#8B5CF6', 'border' => '#7C3AED'],
                'TDS Collected' => ['bg' => '#EC4899', 'border' => '#DB2777'],
            ]
        );

        $summary = [
            'total_commissions' => $commissionData->sum('aggregate'),
            'total_tds' => $tdsData->sum('aggregate'),
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get top earners for a period (Admin)
     */
    public function getTopEarners(
        string $period = 'month',
        ?string $startDate = null,
        ?string $endDate = null,
        int $limit = 10,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);

        $topEarners = AffiliateCommission::query()
            ->where('status', CommissionStatusCast::PAID)
            ->where('type', '!=', CommissionTypeCast::REVERSAL)
            ->whereBetween('commission_date', [$dates['start'], $dates['end']])
            ->selectRaw('user_id, SUM(net_amount) as total_earnings, COUNT(*) as commission_count')
            ->groupBy('user_id')
            ->orderByDesc('total_earnings')
            ->limit($limit)
            ->with('user:id,name,email,uuid')
            ->get();

        $labels = [];
        $data = [];
        $breakdown = [];

        foreach ($topEarners as $earner) {
            $userName = $earner->user?->name ?? 'Unknown';
            $labels[] = $userName;
            $amount = $inRupees ? $this->paisaToRupees((int) $earner->total_earnings) : (int) $earner->total_earnings;
            $data[] = $amount;
            $breakdown[] = [
                'user_id' => $earner->user_id,
                'user_uuid' => $earner->user?->uuid,
                'name' => $userName,
                'total_earnings' => $amount,
                'commission_count' => $earner->commission_count,
            ];
        }

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Earnings',
                    'data' => $data,
                    'backgroundColor' => '#8B5CF6',
                    'borderColor' => '#7C3AED',
                ],
            ],
        ];

        $summary = [
            'top_earners' => $breakdown,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period);
    }

    /**
     * Get commission type colors
     */
    private function getCommissionTypeColors(): array
    {
        return [
            CommissionTypeCast::SPONSOR_BONUS => '#10B981',
            CommissionTypeCast::LEVEL_COMMISSION => '#3B82F6',
            CommissionTypeCast::MATCHING_BONUS => '#8B5CF6',
            CommissionTypeCast::POOL_BONUS => '#EC4899',
            CommissionTypeCast::LEVEL_ACHIEVEMENT => '#F59E0B',
            CommissionTypeCast::ORIGINATOR_JOINING => '#14B8A6',
            CommissionTypeCast::ORIGINATOR_RECURRING => '#06B6D4',
            CommissionTypeCast::AGENT_SALARY => '#6366F1',
            CommissionTypeCast::INCOME_DEDUCTION => '#EF4444',
            CommissionTypeCast::TASK_COMPLETION => '#84CC16',
            CommissionTypeCast::MILESTONE_BONUS => '#F97316',
            CommissionTypeCast::REFERRAL_BONUS => '#A855F7',
            CommissionTypeCast::PERFORMANCE_BONUS => '#22C55E',
            CommissionTypeCast::PURCHASE_COMMISSION => '#0EA5E9',
            CommissionTypeCast::RENEWAL_BONUS => '#D946EF',
            CommissionTypeCast::ADJUSTMENT => '#78716C',
            CommissionTypeCast::CUSTOM => '#6B7280',
        ];
    }

    /**
     * Get status colors
     */
    private function getStatusColors(): array
    {
        return [
            CommissionStatusCast::PENDING => '#F59E0B',
            CommissionStatusCast::APPROVED => '#3B82F6',
            CommissionStatusCast::PROCESSING => '#8B5CF6',
            CommissionStatusCast::PAID => '#10B981',
            CommissionStatusCast::HELD => '#F97316',
            CommissionStatusCast::CANCELLED => '#6B7280',
            CommissionStatusCast::REVERSED => '#EF4444',
        ];
    }
}
