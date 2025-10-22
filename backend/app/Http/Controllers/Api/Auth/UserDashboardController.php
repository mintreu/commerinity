<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Casts\OrderStatusCast;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mintreu\LaravelMoney\LaravelMoney;

class UserDashboardController extends Controller
{
    /**
     * Get user dashboard statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAccountDashboard(Request $request)
    {
        $user = $request->user();

        // Validate date inputs
        $request->validate([
            'from' => 'nullable|date|before_or_equal:today',
            'to' => 'nullable|date|after_or_equal:from|before_or_equal:today'
        ]);

        $dateFrom = $request->input('from');
        $dateTo = $request->input('to');

        // Create cache key based on user and date range
        $cacheKey = sprintf(
            'dashboard_stats_%d_%s_%s',
            $user->id,
            $dateFrom ?? 'all',
            $dateTo ?? 'all'
        );

        // Cache for 5 minutes (adjust as needed)
        $stats = Cache::remember($cacheKey, 300, function () use ($user, $dateFrom, $dateTo) {
            return $this->calculateDashboardStats($user, $dateFrom, $dateTo);
        });

        return response()->json(['data' => $stats]);
    }

    /**
     * Calculate all dashboard statistics
     *
     * @param User $user
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return array
     */
    private function calculateDashboardStats($user, ?string $dateFrom, ?string $dateTo): array
    {
        // Load wallet separately (not affected by date filter)
        $user->loadMissing(['wallet', 'level']);

        // Calculate earnings
        $totalEarnings = $this->calculateTotalEarnings($user, $dateFrom, $dateTo);
        $directEarnings = $this->calculateDirectEarnings($user, $dateFrom, $dateTo);
        $teamEarnings = $this->calculateTeamEarnings($user, $dateFrom, $dateTo);

        // Get order statistics (single optimized query)
        $orderStats = $this->getOrderStatistics($user, $dateFrom, $dateTo);

        // Get referral count
        $totalReferrals = $this->getTotalReferrals($user, $dateFrom, $dateTo);

        // Current rank (not affected by date filter)
        $currentRank = $user->level?->name ?? 'N/A';

        return [
            'wallet_balance' => [
                'label' => 'Wallet Balance',
                'value' => LaravelMoney::format($user->wallet?->balance ?? 0),
                'change' => null,
                'trend' => 'neutral'
            ],
            'total_earnings' => [
                'label' => 'Total Earnings',
                'value' => LaravelMoney::format($totalEarnings),
                'change' => $this->calculateTrendPercentage($totalEarnings, 'earnings', $dateFrom, $dateTo),
                'trend' => $this->getTrendDirection($totalEarnings, 'earnings', $dateFrom, $dateTo)
            ],
            'direct_earnings' => [
                'label' => 'Direct Earnings',
                'value' => LaravelMoney::format($directEarnings),
                'change' => $this->calculateTrendPercentage($directEarnings, 'direct_earnings', $dateFrom, $dateTo),
                'trend' => $this->getTrendDirection($directEarnings, 'direct_earnings', $dateFrom, $dateTo)
            ],
            'team_earnings' => [
                'label' => 'Team Earnings',
                'value' => LaravelMoney::format($teamEarnings),
                'change' => $this->calculateTrendPercentage($teamEarnings, 'team_earnings', $dateFrom, $dateTo),
                'trend' => $this->getTrendDirection($teamEarnings, 'team_earnings', $dateFrom, $dateTo)
            ],
            'pending_orders' => [
                'label' => 'Pending Orders',
                'value' => (string) $orderStats['pending'],
                'change' => $this->calculateTrendPercentage($orderStats['pending'], 'pending_orders', $dateFrom, $dateTo),
                'trend' => $this->getTrendDirection($orderStats['pending'], 'pending_orders', $dateFrom, $dateTo)
            ],
            'total_referrals' => [
                'label' => 'My Referrals',
                'value' => (string) $totalReferrals,
                'change' => $this->calculateTrendPercentage($totalReferrals, 'referrals', $dateFrom, $dateTo),
                'trend' => $this->getTrendDirection($totalReferrals, 'referrals', $dateFrom, $dateTo)
            ],
            'total_orders' => [
                'label' => 'My Orders',
                'value' => (string) $orderStats['total'],
                'change' => $this->calculateTrendPercentage($orderStats['total'], 'orders', $dateFrom, $dateTo),
                'trend' => $this->getTrendDirection($orderStats['total'], 'orders', $dateFrom, $dateTo)
            ],
            'completed_orders' => [
                'label' => 'Completed Orders',
                'value' => (string) $orderStats['completed'],
                'change' => $this->calculateTrendPercentage($orderStats['completed'], 'completed_orders', $dateFrom, $dateTo),
                'trend' => $this->getTrendDirection($orderStats['completed'], 'completed_orders', $dateFrom, $dateTo)
            ],
            'current_rank' => [
                'label' => 'Current Rank',
                'value' => $currentRank,
                'change' => null,
                'trend' => 'neutral'
            ]
        ];
    }

    /**
     * Get order statistics with a single optimized query
     *
     * @param User $user
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return array
     */
    private function getOrderStatistics($user, ?string $dateFrom, ?string $dateTo): array
    {
        $query = $user->orders()
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo));

        // Get all counts in a single query using selectRaw
        $stats = $query->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as pending
        ', [
            OrderStatusCast::COMPLETED->value,
            OrderStatusCast::PENDING->value,
            OrderStatusCast::PROCESSING->value
        ])->first();

        return [
            'total' => (int) ($stats->total ?? 0),
            'completed' => (int) ($stats->completed ?? 0),
            'pending' => (int) ($stats->pending ?? 0)
        ];
    }

    /**
     * Calculate total earnings for user
     *
     * @param User $user
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return float
     */
    private function calculateTotalEarnings($user, ?string $dateFrom, ?string $dateTo): float
    {
        // TODO: Replace with your actual earnings relationship/query
        // Example implementation:
        /*
        return $user->earnings()
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->sum('amount');
        */

        return 15200.00; // Demo value
    }

    /**
     * Calculate direct earnings (self-generated)
     *
     * @param User $user
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return float
     */
    private function calculateDirectEarnings($user, ?string $dateFrom, ?string $dateTo): float
    {
        // TODO: Replace with your actual direct earnings query
        // Example implementation:
        /*
        return $user->earnings()
            ->where('type', 'direct')
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->sum('amount');
        */

        return 8500.00; // Demo value
    }

    /**
     * Calculate team earnings (from downline)
     *
     * @param User $user
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return float
     */
    private function calculateTeamEarnings($user, ?string $dateFrom, ?string $dateTo): float
    {
        // TODO: Replace with your actual team earnings query
        // Example implementation:
        /*
        return $user->earnings()
            ->where('type', 'team')
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->sum('amount');
        */

        return 6700.00; // Demo value
    }

    /**
     * Get total referrals count
     *
     * @param User $user
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return int
     */
    private function getTotalReferrals($user, ?string $dateFrom, ?string $dateTo): int
    {
        // Use query builder (not loaded collection)
        return $user->descendants()
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->count();
    }

    /**
     * Calculate trend percentage compared to previous period
     *
     * @param mixed $currentValue
     * @param string $type
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return string|null
     */
    private function calculateTrendPercentage($currentValue, string $type, ?string $dateFrom, ?string $dateTo): ?string
    {
        // TODO: Implement actual trend calculation
        // Calculate previous period and compare
        /*
        if (!$dateFrom || !$dateTo) {
            return null;
        }

        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        $periodLength = $start->diffInDays($end);

        $previousStart = $start->copy()->subDays($periodLength + 1);
        $previousEnd = $start->copy()->subDay();

        $previousValue = $this->calculateValueForPeriod($type, $previousStart, $previousEnd);

        if ($previousValue == 0) {
            return $currentValue > 0 ? '+100%' : null;
        }

        $change = (($currentValue - $previousValue) / $previousValue) * 100;
        $sign = $change >= 0 ? '+' : '';

        return $sign . number_format($change, 1) . '%';
        */

        // Demo values
        $trends = [
            'earnings' => '+12.5%',
            'direct_earnings' => '+8.3%',
            'team_earnings' => '+15.2%',
            'pending_orders' => '-5.2%',
            'referrals' => '+8.3%',
            'orders' => '+15.7%',
            'completed_orders' => '+12.1%'
        ];

        return $trends[$type] ?? null;
    }

    /**
     * Get trend direction (up, down, neutral)
     *
     * @param mixed $currentValue
     * @param string $type
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return string
     */
    private function getTrendDirection($currentValue, string $type, ?string $dateFrom, ?string $dateTo): string
    {
        $change = $this->calculateTrendPercentage($currentValue, $type, $dateFrom, $dateTo);

        if (!$change) {
            return 'neutral';
        }

        if (str_starts_with($change, '+')) {
            return 'up';
        } elseif (str_starts_with($change, '-')) {
            return 'down';
        }

        return 'neutral';
    }

    /**
     * Clear dashboard cache for user
     * Call this when user data changes (orders, earnings, etc.)
     *
     * @param User $user
     * @return void
     */
    public function clearDashboardCache($user): void
    {
        Cache::forget('dashboard_stats_' . $user->id . '_*');
    }
}
