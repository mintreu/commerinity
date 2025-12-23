<?php

declare(strict_types=1);

namespace App\Services\Trends;

use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\Transaction;
use App\Models\Wallet;
use Flowframe\Trend\Trend;

/**
 * WalletTrendService - Wallet balance and activity trends
 *
 * Charts:
 * - Balance history over time
 * - Credit vs Debit comparison
 * - Total credits/debits over time
 */
final class WalletTrendService extends BaseTrendService
{
    /**
     * Get balance history for a wallet
     *
     * Uses balance_after from completed transactions
     */
    public function getBalanceHistory(
        int $walletId,
        string $period = 'month',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $query = Transaction::query()
            ->where('wallet_id', $walletId)
            ->where('status', TransactionStatusCast::COMPLETED)
            ->whereNotNull('balance_after');

        $trend = Trend::query($query)
            ->between(start: $dates['start'], end: $dates['end']);

        $data = match ($interval) {
            'hour' => $trend->perHour()->average('balance_after'),
            'day' => $trend->perDay()->average('balance_after'),
            'week' => $trend->perDay()->average('balance_after'), // No perWeek, use day
            'month' => $trend->perMonth()->average('balance_after'),
            'year' => $trend->perYear()->average('balance_after'),
            default => $trend->perDay()->average('balance_after'),
        };

        if ($inRupees) {
            $data = $this->convertCollectionToRupees($data);
        }

        $chartData = $this->formatForChart(
            $data,
            'Balance',
            '#10B981',
            '#059669'
        );

        $wallet = Wallet::find($walletId);
        $summary = [
            'current_balance' => $inRupees ? $this->paisaToRupees($wallet?->balance ?? 0) : ($wallet?->balance ?? 0),
            'available_balance' => $inRupees ? $this->paisaToRupees($wallet?->available_balance ?? 0) : ($wallet?->available_balance ?? 0),
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get credit and debit comparison trends
     */
    public function getCreditDebitTrend(
        int $walletId,
        string $period = 'month',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $baseQuery = fn () => Transaction::query()
            ->where('wallet_id', $walletId)
            ->where('status', TransactionStatusCast::COMPLETED);

        // Credits
        $creditTrend = Trend::query($baseQuery()->where('type', TransactionTypeCast::CREDIT))
            ->between(start: $dates['start'], end: $dates['end']);

        // Debits
        $debitTrend = Trend::query($baseQuery()->where('type', TransactionTypeCast::DEBIT))
            ->between(start: $dates['start'], end: $dates['end']);

        $creditData = match ($interval) {
            'hour' => $creditTrend->perHour()->sum('amount'),
            'day' => $creditTrend->perDay()->sum('amount'),
            'month' => $creditTrend->perMonth()->sum('amount'),
            'year' => $creditTrend->perYear()->sum('amount'),
            default => $creditTrend->perDay()->sum('amount'),
        };

        $debitData = match ($interval) {
            'hour' => $debitTrend->perHour()->sum('amount'),
            'day' => $debitTrend->perDay()->sum('amount'),
            'month' => $debitTrend->perMonth()->sum('amount'),
            'year' => $debitTrend->perYear()->sum('amount'),
            default => $debitTrend->perDay()->sum('amount'),
        };

        if ($inRupees) {
            $creditData = $this->convertCollectionToRupees($creditData);
            $debitData = $this->convertCollectionToRupees($debitData);
        }

        $chartData = $this->formatMultipleForChart(
            [
                'Credits' => $creditData,
                'Debits' => $debitData,
            ],
            [
                'Credits' => ['bg' => '#10B981', 'border' => '#059669'],
                'Debits' => ['bg' => '#EF4444', 'border' => '#DC2626'],
            ]
        );

        $totalCredits = $creditData->sum('aggregate');
        $totalDebits = $debitData->sum('aggregate');

        $summary = [
            'total_credits' => $totalCredits,
            'total_debits' => $totalDebits,
            'net_change' => $totalCredits - $totalDebits,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get total wallet activity (credits + debits volume)
     */
    public function getActivityVolume(
        int $walletId,
        string $period = 'month',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $query = Transaction::query()
            ->where('wallet_id', $walletId)
            ->where('status', TransactionStatusCast::COMPLETED);

        $trend = Trend::query($query)
            ->between(start: $dates['start'], end: $dates['end']);

        $volumeData = match ($interval) {
            'hour' => $trend->perHour()->sum('amount'),
            'day' => $trend->perDay()->sum('amount'),
            'month' => $trend->perMonth()->sum('amount'),
            'year' => $trend->perYear()->sum('amount'),
            default => $trend->perDay()->sum('amount'),
        };

        $countTrend = Trend::query(
            Transaction::query()
                ->where('wallet_id', $walletId)
                ->where('status', TransactionStatusCast::COMPLETED)
        )
            ->between(start: $dates['start'], end: $dates['end']);

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
                'Count' => $countData,
            ],
            [
                'Volume' => ['bg' => '#6366F1', 'border' => '#4F46E5'],
                'Count' => ['bg' => '#F59E0B', 'border' => '#D97706'],
            ]
        );

        $summary = [
            'total_volume' => $volumeData->sum('aggregate'),
            'total_transactions' => $countData->sum('aggregate'),
            'average_transaction' => $countData->sum('aggregate') > 0
                ? round($volumeData->sum('aggregate') / $countData->sum('aggregate'), 2)
                : 0,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get wallet statistics comparison (current vs previous period)
     */
    public function getComparisonStats(int $walletId, string $period = 'month'): array
    {
        $currentDates = $this->parsePeriod($period);
        $previousDates = $this->getPreviousPeriodDates($period);

        $baseQuery = fn () => Transaction::query()
            ->where('wallet_id', $walletId)
            ->where('status', TransactionStatusCast::COMPLETED);

        // Current period stats
        $currentCredits = (int) $baseQuery()
            ->where('type', TransactionTypeCast::CREDIT)
            ->whereBetween('created_at', [$currentDates['start'], $currentDates['end']])
            ->sum('amount');

        $currentDebits = (int) $baseQuery()
            ->where('type', TransactionTypeCast::DEBIT)
            ->whereBetween('created_at', [$currentDates['start'], $currentDates['end']])
            ->sum('amount');

        $currentCount = $baseQuery()
            ->whereBetween('created_at', [$currentDates['start'], $currentDates['end']])
            ->count();

        // Previous period stats
        $previousCredits = (int) $baseQuery()
            ->where('type', TransactionTypeCast::CREDIT)
            ->whereBetween('created_at', [$previousDates['start'], $previousDates['end']])
            ->sum('amount');

        $previousDebits = (int) $baseQuery()
            ->where('type', TransactionTypeCast::DEBIT)
            ->whereBetween('created_at', [$previousDates['start'], $previousDates['end']])
            ->sum('amount');

        $previousCount = $baseQuery()
            ->whereBetween('created_at', [$previousDates['start'], $previousDates['end']])
            ->count();

        return [
            'success' => true,
            'data' => [
                'current' => [
                    'credits' => $this->paisaToRupees($currentCredits),
                    'debits' => $this->paisaToRupees($currentDebits),
                    'net' => $this->paisaToRupees($currentCredits - $currentDebits),
                    'count' => $currentCount,
                ],
                'previous' => [
                    'credits' => $this->paisaToRupees($previousCredits),
                    'debits' => $this->paisaToRupees($previousDebits),
                    'net' => $this->paisaToRupees($previousCredits - $previousDebits),
                    'count' => $previousCount,
                ],
                'changes' => [
                    'credits_change' => $this->calculatePercentageChange($currentCredits, $previousCredits),
                    'debits_change' => $this->calculatePercentageChange($currentDebits, $previousDebits),
                    'count_change' => $this->calculatePercentageChange($currentCount, $previousCount),
                ],
            ],
            'meta' => [
                'period' => $period,
                'current_period' => [
                    'start' => $currentDates['start']->toDateString(),
                    'end' => $currentDates['end']->toDateString(),
                ],
                'previous_period' => [
                    'start' => $previousDates['start']->toDateString(),
                    'end' => $previousDates['end']->toDateString(),
                ],
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Get previous period dates for comparison
     */
    private function getPreviousPeriodDates(string $period): array
    {
        return match ($period) {
            'today' => [
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay(),
            ],
            'week' => [
                'start' => now()->subWeek()->startOfWeek(),
                'end' => now()->subWeek()->endOfWeek(),
            ],
            'month' => [
                'start' => now()->subMonth()->startOfMonth(),
                'end' => now()->subMonth()->endOfMonth(),
            ],
            'quarter' => [
                'start' => now()->subQuarter()->startOfQuarter(),
                'end' => now()->subQuarter()->endOfQuarter(),
            ],
            'year' => [
                'start' => now()->subYear()->startOfYear(),
                'end' => now()->subYear()->endOfYear(),
            ],
            default => [
                'start' => now()->subMonth()->startOfMonth(),
                'end' => now()->subMonth()->endOfMonth(),
            ],
        };
    }
}
