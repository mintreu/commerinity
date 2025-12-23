<?php

declare(strict_types=1);

namespace App\Services\Trends;

use App\Casts\KycStatusCast;
use App\Casts\TransactionStatusCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Casts\WalletStatusCast;
use App\Models\Kyc;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Flowframe\Trend\Trend;

/**
 * AdminTrendService - Platform-wide analytics (Admin only)
 *
 * Charts:
 * - User registration trends
 * - Platform revenue
 * - KYC trends
 * - Wallet health metrics
 * - Overall platform statistics
 */
final class AdminTrendService extends BaseTrendService
{
    /**
     * Get user registration trend
     */
    public function getUserRegistrationTrend(
        string $period = 'year',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $userType = null
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $query = User::query();

        if ($userType && $userType !== 'all') {
            $type = UserTypeCast::tryFrom($userType);
            if ($type) {
                $query->where('type', $type);
            }
        }

        $trend = Trend::query($query)
            ->between(start: $dates['start'], end: $dates['end']);

        $data = match ($interval) {
            'hour' => $trend->perHour()->count(),
            'day' => $trend->perDay()->count(),
            'month' => $trend->perMonth()->count(),
            'year' => $trend->perYear()->count(),
            default => $trend->perMonth()->count(),
        };

        $chartData = $this->formatForChart(
            $data,
            'New Users',
            '#6366F1',
            '#4F46E5'
        );

        $totalUsers = User::count();
        $activeUsers = User::where('status', UserStatusCast::ACTIVE)->count();
        $newThisPeriod = (int) $data->sum('aggregate');

        $summary = [
            'new_users' => $newThisPeriod,
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'growth_rate' => $totalUsers > $newThisPeriod
                ? round(($newThisPeriod / ($totalUsers - $newThisPeriod)) * 100, 2)
                : 100,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get user registration by type
     */
    public function getUsersByType(
        string $period = 'year',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $types = [
            UserTypeCast::REGULAR,
            UserTypeCast::MEMBER,
            UserTypeCast::PROMOTER,
            UserTypeCast::ADVISOR,
        ];

        $datasets = [];
        $colors = $this->getUserTypeColors();

        foreach ($types as $type) {
            $trend = Trend::query(User::where('type', $type))
                ->between(start: $dates['start'], end: $dates['end']);

            $data = match ($interval) {
                'day' => $trend->perDay()->count(),
                'month' => $trend->perMonth()->count(),
                'year' => $trend->perYear()->count(),
                default => $trend->perMonth()->count(),
            };

            $datasets[$type->getLabel()] = $data;
        }

        $chartData = $this->formatMultipleForChart($datasets, [
            'Regular' => ['bg' => $colors[UserTypeCast::REGULAR->value], 'border' => $colors[UserTypeCast::REGULAR->value]],
            'Member' => ['bg' => $colors[UserTypeCast::MEMBER->value], 'border' => $colors[UserTypeCast::MEMBER->value]],
            'Promoter' => ['bg' => $colors[UserTypeCast::PROMOTER->value], 'border' => $colors[UserTypeCast::PROMOTER->value]],
            'Advisor' => ['bg' => $colors[UserTypeCast::ADVISOR->value], 'border' => $colors[UserTypeCast::ADVISOR->value]],
        ]);

        // Current distribution
        $distribution = [];
        foreach (UserTypeCast::cases() as $type) {
            $distribution[$type->value] = User::where('type', $type)->count();
        }

        $summary = [
            'distribution' => $distribution,
            'total_users' => array_sum($distribution),
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get platform revenue trend (fees + tax)
     */
    public function getRevenueTrend(
        string $period = 'year',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $query = Transaction::query()
            ->where('status', TransactionStatusCast::COMPLETED);

        $feeTrend = Trend::query(clone $query)
            ->between(start: $dates['start'], end: $dates['end']);

        $taxTrend = Trend::query(clone $query)
            ->between(start: $dates['start'], end: $dates['end']);

        $feeData = match ($interval) {
            'day' => $feeTrend->perDay()->sum('fee'),
            'month' => $feeTrend->perMonth()->sum('fee'),
            'year' => $feeTrend->perYear()->sum('fee'),
            default => $feeTrend->perMonth()->sum('fee'),
        };

        $taxData = match ($interval) {
            'day' => $taxTrend->perDay()->sum('tax'),
            'month' => $taxTrend->perMonth()->sum('tax'),
            'year' => $taxTrend->perYear()->sum('tax'),
            default => $taxTrend->perMonth()->sum('tax'),
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
                'Fees' => ['bg' => '#10B981', 'border' => '#059669'],
                'Tax' => ['bg' => '#F59E0B', 'border' => '#D97706'],
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
     * Get transaction volume trend (platform-wide)
     */
    public function getTransactionVolumeTrend(
        string $period = 'year',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null,
        bool $inRupees = true
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        $query = Transaction::query()
            ->where('status', TransactionStatusCast::COMPLETED);

        $trend = Trend::query($query)
            ->between(start: $dates['start'], end: $dates['end']);

        $volumeData = match ($interval) {
            'hour' => $trend->perHour()->sum('amount'),
            'day' => $trend->perDay()->sum('amount'),
            'month' => $trend->perMonth()->sum('amount'),
            'year' => $trend->perYear()->sum('amount'),
            default => $trend->perMonth()->sum('amount'),
        };

        $countTrend = Trend::query(
            Transaction::query()->where('status', TransactionStatusCast::COMPLETED)
        )
            ->between(start: $dates['start'], end: $dates['end']);

        $countData = match ($interval) {
            'hour' => $countTrend->perHour()->count(),
            'day' => $countTrend->perDay()->count(),
            'month' => $countTrend->perMonth()->count(),
            'year' => $countTrend->perYear()->count(),
            default => $countTrend->perMonth()->count(),
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
                'Volume' => ['bg' => '#8B5CF6', 'border' => '#7C3AED'],
                'Count' => ['bg' => '#06B6D4', 'border' => '#0891B2'],
            ]
        );

        $summary = [
            'total_volume' => $volumeData->sum('aggregate'),
            'total_transactions' => (int) $countData->sum('aggregate'),
            'average_transaction' => $countData->sum('aggregate') > 0
                ? round($volumeData->sum('aggregate') / $countData->sum('aggregate'), 2)
                : 0,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get KYC approval trends
     */
    public function getKycTrend(
        string $period = 'year',
        ?string $interval = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $dates = $this->parsePeriod($period, $startDate, $endDate);
        $interval = $this->getIntervalForPeriod($period, $interval);

        // Submitted KYCs
        $submittedTrend = Trend::model(Kyc::class)
            ->between(start: $dates['start'], end: $dates['end']);

        // Approved KYCs
        $approvedTrend = Trend::query(Kyc::where('status', KycStatusCast::APPROVED))
            ->dateColumn('updated_at')
            ->between(start: $dates['start'], end: $dates['end']);

        // Rejected KYCs
        $rejectedTrend = Trend::query(Kyc::where('status', KycStatusCast::REJECTED))
            ->dateColumn('updated_at')
            ->between(start: $dates['start'], end: $dates['end']);

        $submittedData = match ($interval) {
            'day' => $submittedTrend->perDay()->count(),
            'month' => $submittedTrend->perMonth()->count(),
            'year' => $submittedTrend->perYear()->count(),
            default => $submittedTrend->perMonth()->count(),
        };

        $approvedData = match ($interval) {
            'day' => $approvedTrend->perDay()->count(),
            'month' => $approvedTrend->perMonth()->count(),
            'year' => $approvedTrend->perYear()->count(),
            default => $approvedTrend->perMonth()->count(),
        };

        $rejectedData = match ($interval) {
            'day' => $rejectedTrend->perDay()->count(),
            'month' => $rejectedTrend->perMonth()->count(),
            'year' => $rejectedTrend->perYear()->count(),
            default => $rejectedTrend->perMonth()->count(),
        };

        $chartData = $this->formatMultipleForChart(
            [
                'Submitted' => $submittedData,
                'Approved' => $approvedData,
                'Rejected' => $rejectedData,
            ],
            [
                'Submitted' => ['bg' => '#6366F1', 'border' => '#4F46E5'],
                'Approved' => ['bg' => '#10B981', 'border' => '#059669'],
                'Rejected' => ['bg' => '#EF4444', 'border' => '#DC2626'],
            ]
        );

        // Current status counts
        $pendingCount = Kyc::where('status', KycStatusCast::PENDING)->count();
        $totalApproved = Kyc::where('status', KycStatusCast::APPROVED)->count();
        $totalRejected = Kyc::where('status', KycStatusCast::REJECTED)->count();

        $summary = [
            'submitted' => (int) $submittedData->sum('aggregate'),
            'approved' => (int) $approvedData->sum('aggregate'),
            'rejected' => (int) $rejectedData->sum('aggregate'),
            'pending_now' => $pendingCount,
            'total_approved' => $totalApproved,
            'approval_rate' => ($totalApproved + $totalRejected) > 0
                ? round(($totalApproved / ($totalApproved + $totalRejected)) * 100, 2)
                : 0,
            'period_start' => $dates['start']->toDateString(),
            'period_end' => $dates['end']->toDateString(),
        ];

        return $this->buildResponse($chartData, $summary, $period, $interval);
    }

    /**
     * Get wallet health metrics
     */
    public function getWalletHealth(bool $inRupees = true): array
    {
        $totalWallets = Wallet::count();
        $activeWallets = Wallet::where('status', WalletStatusCast::ACTIVE)->count();
        $suspendedWallets = Wallet::where('status', WalletStatusCast::SUSPENDED)->count();

        $totalBalance = (int) Wallet::sum('balance');
        $totalHoldBalance = (int) Wallet::sum('hold_balance');
        $averageBalance = $totalWallets > 0 ? (int) ($totalBalance / $totalWallets) : 0;

        $zeroBalanceWallets = Wallet::where('balance', 0)->count();
        $walletsWithHold = Wallet::where('hold_balance', '>', 0)->count();

        // Balance distribution
        $distribution = [
            '0' => Wallet::where('balance', 0)->count(),
            '1-1000' => Wallet::whereBetween('balance', [1, 100000])->count(), // 1-1000 Rs in paisa
            '1000-10000' => Wallet::whereBetween('balance', [100001, 1000000])->count(),
            '10000-100000' => Wallet::whereBetween('balance', [1000001, 10000000])->count(),
            '100000+' => Wallet::where('balance', '>', 10000000)->count(),
        ];

        return [
            'success' => true,
            'data' => [
                'total_wallets' => $totalWallets,
                'active_wallets' => $activeWallets,
                'suspended_wallets' => $suspendedWallets,
                'total_balance' => $inRupees ? $this->paisaToRupees($totalBalance) : $totalBalance,
                'total_hold' => $inRupees ? $this->paisaToRupees($totalHoldBalance) : $totalHoldBalance,
                'average_balance' => $inRupees ? $this->paisaToRupees($averageBalance) : $averageBalance,
                'zero_balance_count' => $zeroBalanceWallets,
                'with_hold_count' => $walletsWithHold,
                'balance_distribution' => $distribution,
            ],
            'meta' => [
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Get platform overview dashboard stats
     */
    public function getPlatformOverview(bool $inRupees = true): array
    {
        // Users
        $totalUsers = User::count();
        $activeMembers = User::where('type', '!=', UserTypeCast::REGULAR)->count();
        $newUsersToday = User::whereDate('created_at', today())->count();
        $newUsersThisMonth = User::whereBetween('created_at', [now()->startOfMonth(), now()])->count();

        // Transactions
        $totalTransactions = Transaction::where('status', TransactionStatusCast::COMPLETED)->count();
        $totalVolume = (int) Transaction::where('status', TransactionStatusCast::COMPLETED)->sum('amount');
        $todayVolume = (int) Transaction::where('status', TransactionStatusCast::COMPLETED)
            ->whereDate('created_at', today())
            ->sum('amount');
        $monthVolume = (int) Transaction::where('status', TransactionStatusCast::COMPLETED)
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->sum('amount');

        // Revenue
        $totalFees = (int) Transaction::where('status', TransactionStatusCast::COMPLETED)->sum('fee');
        $totalTax = (int) Transaction::where('status', TransactionStatusCast::COMPLETED)->sum('tax');

        // Wallets
        $totalWalletBalance = (int) Wallet::sum('balance');

        // KYC
        $pendingKyc = Kyc::where('status', KycStatusCast::PENDING)->count();

        return [
            'success' => true,
            'data' => [
                'users' => [
                    'total' => $totalUsers,
                    'active_members' => $activeMembers,
                    'new_today' => $newUsersToday,
                    'new_this_month' => $newUsersThisMonth,
                ],
                'transactions' => [
                    'total_count' => $totalTransactions,
                    'total_volume' => $inRupees ? $this->paisaToRupees($totalVolume) : $totalVolume,
                    'today_volume' => $inRupees ? $this->paisaToRupees($todayVolume) : $todayVolume,
                    'month_volume' => $inRupees ? $this->paisaToRupees($monthVolume) : $monthVolume,
                ],
                'revenue' => [
                    'total_fees' => $inRupees ? $this->paisaToRupees($totalFees) : $totalFees,
                    'total_tax' => $inRupees ? $this->paisaToRupees($totalTax) : $totalTax,
                    'total' => $inRupees ? $this->paisaToRupees($totalFees + $totalTax) : ($totalFees + $totalTax),
                ],
                'wallets' => [
                    'total_balance' => $inRupees ? $this->paisaToRupees($totalWalletBalance) : $totalWalletBalance,
                ],
                'kyc' => [
                    'pending' => $pendingKyc,
                ],
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
            UserTypeCast::APPLICANT->value => '#9CA3AF',
        ];
    }
}
