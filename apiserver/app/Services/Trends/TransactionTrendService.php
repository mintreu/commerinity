<?php

declare(strict_types=1);

namespace App\Services\Trends;

use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Models\Transaction;
use Flowframe\Trend\Trend;

/**
 * TransactionTrendService - Transaction analytics and trends
 *
 * Charts:
 * - Transaction volume over time
 * - Transactions by payment method
 * - Transaction status distribution
 * - Success/failure rates
 */
final class TransactionTrendService extends BaseTrendService
{
    /**
     * Get transaction volume trend (amount and count)
     */
    public function getVolumeTrend(
        string $period = 'month',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $walletId = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $query = Transaction::query()
            ->where('status', TransactionStatusCast::COMPLETED);

        if ($walletId) {
            $query->where('wallet_id', $walletId);
        }

        $volumeTrend = Trend::query(clone $query)
            ->between(start: $dates['start'], end: $dates['end']);

        $countTrend = Trend::query(clone $query)
            ->between(start: $dates['start'], end: $dates['end']);

        $volumeData = match ($interval) {
            'hour' => $volumeTrend->perHour()->sum('amount'),
            'day' => $volumeTrend->perDay()->sum('amount'),
            'month' => $volumeTrend->perMonth()->sum('amount'),
            'year' => $volumeTrend->perYear()->sum('amount'),
            default => $volumeTrend->perDay()->sum('amount'),
        };

        $countData = match ($interval) {
            'hour' => $countTrend->perHour()->count(),
            'day' => $countTrend->perDay()->count(),
            'month' => $countTrend->perMonth()->count(),
            'year' => $countTrend->perYear()->count(),
            default => $countTrend->perDay()->count(),
        };

        if ($inRupees) {
            $volumeData = $this->convertCollectionToRupees($volumeData);
        }

        $chartData = $this->formatMultipleForChart(
            [
                'Volume' => $volumeData,
                'Transactions' => $countData,
            ],
            [
                'Volume' => ['bg' => '#8B5CF6', 'border' => '#7C3AED'],
                'Transactions' => ['bg' => '#06B6D4', 'border' => '#0891B2'],
            ]
        );

        $summary = [
            'total_volume' => $volumeData->sum('aggregate'),
            'total_count' => (int) $countData->sum('aggregate'),
            'average_amount' => $countData->sum('aggregate') > 0
                ? round($volumeData->sum('aggregate') / $countData->sum('aggregate'), 2)
                : 0,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get transactions breakdown by payment method
     */
    public function getByPaymentMethod(
        string $period = 'month',
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $walletId = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);

        $query = Transaction::query()
            ->where('status', TransactionStatusCast::COMPLETED)
            ->whereBetween('created_at', [$dates['start'], $dates['end']]);

        if ($walletId) {
            $query->where('wallet_id', $walletId);
        }

        $methods = PaymentMethodCast::cases();
        $data = [];
        $colors = $this->getPaymentMethodColors();

        foreach ($methods as $method) {
            $amount = (int) (clone $query)
                ->where('payment_method', $method)
                ->sum('amount');

            $count = (clone $query)
                ->where('payment_method', $method)
                ->count();

            if ($amount > 0 || $count > 0) {
                $data[$method->value] = [
                    'label' => $method->getLabel(),
                    'amount' => $inRupees ? $this->paisaToRupees($amount) : $amount,
                    'count' => $count,
                    'color' => $colors[$method->value] ?? '#6B7280',
                ];
            }
        }

        $labels = array_column($data, 'label');
        $amounts = array_column($data, 'amount');
        $counts = array_column($data, 'count');
        $bgColors = array_column($data, 'color');

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Amount',
                    'data' => $amounts,
                    'backgroundColor' => $bgColors,
                ],
            ],
        ];

        $summary = [
            'total_amount' => array_sum($amounts),
            'total_count' => array_sum($counts),
            'breakdown' => $data,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period);
    }

    /**
     * Get transaction status distribution
     */
    public function getStatusDistribution(
        string $period = 'month',
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $walletId = null
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);

        $query = Transaction::query()
            ->whereBetween('created_at', [$dates['start'], $dates['end']]);

        if ($walletId) {
            $query->where('wallet_id', $walletId);
        }

        $statuses = TransactionStatusCast::cases();
        $data = [];
        $colors = $this->getStatusColors();

        foreach ($statuses as $status) {
            $count = (clone $query)
                ->where('status', $status)
                ->count();

            if ($count > 0) {
                $data[$status->value] = [
                    'label' => $status->getLabel(),
                    'count' => $count,
                    'color' => $colors[$status->value] ?? '#6B7280',
                ];
            }
        }

        $labels = array_column($data, 'label');
        $counts = array_column($data, 'count');
        $bgColors = array_column($data, 'color');

        $total = array_sum($counts);
        $completed = $data[TransactionStatusCast::COMPLETED->value]['count'] ?? 0;
        $failed = $data[TransactionStatusCast::FAILED->value]['count'] ?? 0;

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
            'total' => $total,
            'success_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'failure_rate' => $total > 0 ? round(($failed / $total) * 100, 2) : 0,
            'breakdown' => $data,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period);
    }

    /**
     * Get success vs failure trend over time
     */
    public function getSuccessFailureTrend(
        string $period = 'month',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $walletId = null
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $baseQuery = fn () => Transaction::query()
            ->when($walletId, fn ($q) => $q->where('wallet_id', $walletId));

        $successTrend = Trend::query($baseQuery()->where('status', TransactionStatusCast::COMPLETED))
            ->between(start: $dates['start'], end: $dates['end']);

        $failedTrend = Trend::query($baseQuery()->where('status', TransactionStatusCast::FAILED))
            ->between(start: $dates['start'], end: $dates['end']);

        $pendingTrend = Trend::query($baseQuery()->where('status', TransactionStatusCast::PENDING))
            ->between(start: $dates['start'], end: $dates['end']);

        $successData = match ($interval) {
            'hour' => $successTrend->perHour()->count(),
            'day' => $successTrend->perDay()->count(),
            'month' => $successTrend->perMonth()->count(),
            'year' => $successTrend->perYear()->count(),
            default => $successTrend->perDay()->count(),
        };

        $failedData = match ($interval) {
            'hour' => $failedTrend->perHour()->count(),
            'day' => $failedTrend->perDay()->count(),
            'month' => $failedTrend->perMonth()->count(),
            'year' => $failedTrend->perYear()->count(),
            default => $failedTrend->perDay()->count(),
        };

        $pendingData = match ($interval) {
            'hour' => $pendingTrend->perHour()->count(),
            'day' => $pendingTrend->perDay()->count(),
            'month' => $pendingTrend->perMonth()->count(),
            'year' => $pendingTrend->perYear()->count(),
            default => $pendingTrend->perDay()->count(),
        };

        $chartData = $this->formatMultipleForChart(
            [
                'Completed' => $successData,
                'Failed' => $failedData,
                'Pending' => $pendingData,
            ],
            [
                'Completed' => ['bg' => '#10B981', 'border' => '#059669'],
                'Failed' => ['bg' => '#EF4444', 'border' => '#DC2626'],
                'Pending' => ['bg' => '#F59E0B', 'border' => '#D97706'],
            ]
        );

        $totalSuccess = (int) $successData->sum('aggregate');
        $totalFailed = (int) $failedData->sum('aggregate');
        $totalPending = (int) $pendingData->sum('aggregate');
        $total = $totalSuccess + $totalFailed + $totalPending;

        $summary = [
            'completed' => $totalSuccess,
            'failed' => $totalFailed,
            'pending' => $totalPending,
            'total' => $total,
            'success_rate' => $total > 0 ? round(($totalSuccess / $total) * 100, 2) : 0,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get fee collection trend (Admin)
     */
    public function getFeeCollectionTrend(
        string $period = 'month',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $query = Transaction::query()
            ->where('status', TransactionStatusCast::COMPLETED)
            ->where('fee', '>', 0);

        $trend = Trend::query($query)
            ->between(start: $dates['start'], end: $dates['end']);

        $feeData = match ($interval) {
            'hour' => $trend->perHour()->sum('fee'),
            'day' => $trend->perDay()->sum('fee'),
            'month' => $trend->perMonth()->sum('fee'),
            'year' => $trend->perYear()->sum('fee'),
            default => $trend->perDay()->sum('fee'),
        };

        $taxTrend = Trend::query(
            Transaction::query()
                ->where('status', TransactionStatusCast::COMPLETED)
                ->where('tax', '>', 0)
        )
            ->between(start: $dates['start'], end: $dates['end']);

        $taxData = match ($interval) {
            'hour' => $taxTrend->perHour()->sum('tax'),
            'day' => $taxTrend->perDay()->sum('tax'),
            'month' => $taxTrend->perMonth()->sum('tax'),
            'year' => $taxTrend->perYear()->sum('tax'),
            default => $taxTrend->perDay()->sum('tax'),
        };

        if ($inRupees) {
            $feeData = $this->convertCollectionToRupees($feeData);
            $taxData = $this->convertCollectionToRupees($taxData);
        }

        $chartData = $this->formatMultipleForChart(
            [
                'Fees' => $feeData,
                'Tax' => $taxData,
            ],
            [
                'Fees' => ['bg' => '#8B5CF6', 'border' => '#7C3AED'],
                'Tax' => ['bg' => '#EC4899', 'border' => '#DB2777'],
            ]
        );

        $summary = [
            'total_fees' => $feeData->sum('aggregate'),
            'total_tax' => $taxData->sum('aggregate'),
            'total_revenue' => $feeData->sum('aggregate') + $taxData->sum('aggregate'),
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get payment method colors for charts
     */
    private function getPaymentMethodColors(): array
    {
        return [
            PaymentMethodCast::WALLET->value => '#10B981',
            PaymentMethodCast::CASHFREE->value => '#6366F1',
            PaymentMethodCast::RAZORPAY->value => '#3B82F6',
            PaymentMethodCast::UPI->value => '#8B5CF6',
            PaymentMethodCast::STRIPE->value => '#EC4899',
            PaymentMethodCast::PAYTM->value => '#F59E0B',
            PaymentMethodCast::BANK_TRANSFER->value => '#14B8A6',
            PaymentMethodCast::CASH->value => '#84CC16',
            PaymentMethodCast::COD->value => '#F97316',
            PaymentMethodCast::PAYOUT_BANK->value => '#06B6D4',
            PaymentMethodCast::PAYOUT_UPI->value => '#A855F7',
        ];
    }

    /**
     * Get status colors for charts
     */
    private function getStatusColors(): array
    {
        return [
            TransactionStatusCast::PENDING->value => '#F59E0B',
            TransactionStatusCast::COMPLETED->value => '#10B981',
            TransactionStatusCast::FAILED->value => '#EF4444',
            TransactionStatusCast::CANCELLED->value => '#6B7280',
            TransactionStatusCast::EXPIRED->value => '#9CA3AF',
            TransactionStatusCast::REFUNDED->value => '#8B5CF6',
            TransactionStatusCast::REVERSED->value => '#EC4899',
        ];
    }
}
