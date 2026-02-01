<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Trends\CommissionTrendService;
use App\Services\Trends\TeamTrendService;
use App\Services\Trends\TransactionTrendService;
use App\Services\Trends\WalletTrendService;
use App\Services\UserServices\UserWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * TrendController - API endpoints for dashboard charts and trends
 *
 * Provides trend data for:
 * - Wallet balance and activity
 * - Commission earnings
 * - Team growth
 * - Transaction volume
 */
final class TrendController extends Controller
{
    public function __construct(
        private readonly UserWalletService $walletService,
        private readonly WalletTrendService $walletTrendService,
        private readonly CommissionTrendService $commissionTrendService,
        private readonly TeamTrendService $teamTrendService,
        private readonly TransactionTrendService $transactionTrendService,
    ) {}

    // ========================================
    // Wallet Trends
    // ========================================

    /**
     * Get wallet balance history trend.
     *
     * GET /api/trends/wallet/balance
     */
    public function walletBalance(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'string', 'in:today,yesterday,week,last_week,month,last_month,quarter,year,last_year,custom'],
            'interval' => ['sometimes', 'string', 'in:hour,day,week,month,year'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        return response()->json(
            $this->walletTrendService->getBalanceHistory(
                walletId: $wallet->id,
                period: $request->input('period', 'month'),
                interval: $request->input('interval'),
                startDate: $request->input('start_date'),
                endDate: $request->input('end_date'),
            )
        );
    }

    /**
     * Get wallet credit vs debit comparison trend.
     *
     * GET /api/trends/wallet/credit-debit
     */
    public function walletCreditDebit(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'string', 'in:today,yesterday,week,last_week,month,last_month,quarter,year,last_year,custom'],
            'interval' => ['sometimes', 'string', 'in:hour,day,week,month,year'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        return response()->json(
            $this->walletTrendService->getCreditDebitTrend(
                walletId: $wallet->id,
                period: $request->input('period', 'month'),
                interval: $request->input('interval'),
                startDate: $request->input('start_date'),
                endDate: $request->input('end_date'),
            )
        );
    }

    /**
     * Get wallet activity volume trend.
     *
     * GET /api/trends/wallet/activity
     */
    public function walletActivity(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'string', 'in:today,yesterday,week,last_week,month,last_month,quarter,year,last_year,custom'],
            'interval' => ['sometimes', 'string', 'in:hour,day,week,month,year'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        return response()->json(
            $this->walletTrendService->getActivityVolume(
                walletId: $wallet->id,
                period: $request->input('period', 'month'),
                interval: $request->input('interval'),
                startDate: $request->input('start_date'),
                endDate: $request->input('end_date'),
            )
        );
    }

    /**
     * Get wallet comparison stats (current vs previous period).
     *
     * GET /api/trends/wallet/comparison
     */
    public function walletComparison(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'string', 'in:today,week,month,quarter,year'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        return response()->json(
            $this->walletTrendService->getComparisonStats(
                walletId: $wallet->id,
                period: $request->input('period', 'month'),
            )
        );
    }

    // ========================================
    // Commission Trends
    // ========================================

    /**
     * Get commission earnings trend.
     *
     * GET /api/trends/commissions/earnings
     */
    public function commissionEarnings(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'string', 'in:today,yesterday,week,last_week,month,last_month,quarter,year,last_year,custom'],
            'interval' => ['sometimes', 'string', 'in:hour,day,week,month,year'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();

        return response()->json(
            $this->commissionTrendService->getEarningsTrend(
                userId: $user->id,
                period: $request->input('period', 'month'),
                interval: $request->input('interval'),
                startDate: $request->input('start_date'),
                endDate: $request->input('end_date'),
            )
        );
    }

    /**
     * Get commission earnings by type breakdown.
     *
     * GET /api/trends/commissions/by-type
     */
    public function commissionByType(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'string', 'in:today,yesterday,week,last_week,month,last_month,quarter,year,last_year,custom'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();

        return response()->json(
            $this->commissionTrendService->getEarningsByType(
                userId: $user->id,
                period: $request->input('period', 'month'),
                startDate: $request->input('start_date'),
                endDate: $request->input('end_date'),
            )
        );
    }

    /**
     * Get commission comparison stats (current vs previous period).
     *
     * GET /api/trends/commissions/comparison
     */
    public function commissionComparison(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'string', 'in:today,week,month,quarter,year'],
        ]);

        $user = $request->user();

        return response()->json(
            $this->commissionTrendService->getComparisonStats(
                userId: $user->id,
                period: $request->input('period', 'month'),
            )
        );
    }

    // ========================================
    // Team Trends
    // ========================================

    /**
     * Get team growth trend.
     *
     * GET /api/trends/team/growth
     */
    public function teamGrowth(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'string', 'in:today,yesterday,week,last_week,month,last_month,quarter,year,last_year,custom'],
            'interval' => ['sometimes', 'string', 'in:hour,day,week,month,year'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();

        return response()->json(
            $this->teamTrendService->getTeamGrowth(
                userId: $user->id,
                period: $request->input('period', 'month'),
                interval: $request->input('interval'),
                startDate: $request->input('start_date'),
                endDate: $request->input('end_date'),
            )
        );
    }

    /**
     * Get team level distribution.
     *
     * GET /api/trends/team/levels
     */
    public function teamLevels(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(
            $this->teamTrendService->getLevelDistribution(
                userId: $user->id,
            )
        );
    }

    /**
     * Get team activity trend.
     *
     * GET /api/trends/team/activity
     */
    public function teamActivity(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'string', 'in:today,yesterday,week,last_week,month,last_month,quarter,year,last_year,custom'],
            'interval' => ['sometimes', 'string', 'in:hour,day,week,month,year'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();

        return response()->json(
            $this->teamTrendService->getTeamActivity(
                userId: $user->id,
                period: $request->input('period', 'month'),
                interval: $request->input('interval'),
                startDate: $request->input('start_date'),
                endDate: $request->input('end_date'),
            )
        );
    }

    // ========================================
    // Dashboard Summary
    // ========================================

    /**
     * Get comprehensive dashboard summary with all trends.
     *
     * GET /api/trends/dashboard
     */
    public function dashboardSummary(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'string', 'in:today,week,month,quarter,year'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);
        $period = $request->input('period', 'month');

        // Get wallet comparison
        $walletStats = $this->walletTrendService->getComparisonStats($wallet->id, $period);

        // Get commission comparison
        $commissionStats = $this->commissionTrendService->getComparisonStats($user->id, $period);

        // Get team summary
        $teamStats = $this->teamTrendService->getTeamSummary($user->id);

        return response()->json([
            'success' => true,
            'data' => [
                'wallet' => $walletStats['data'] ?? [],
                'commissions' => $commissionStats['data'] ?? [],
                'team' => $teamStats['data'] ?? [],
            ],
            'meta' => [
                'period' => $period,
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get transaction volume trend (amount and count).
     *
     * GET /api/trends/transactions/volume
     */
    public function transactionVolume(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'string', 'in:today,yesterday,week,last_week,month,last_month,quarter,year,last_year,custom'],
            'interval' => ['sometimes', 'string', 'in:hour,day,week,month,year'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ]);

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        return response()->json(
            $this->transactionTrendService->getVolumeTrend(
                period: $request->input('period', 'month'),
                interval: $request->input('interval'),
                startDate: $request->input('start_date'),
                endDate: $request->input('end_date'),
                walletId: $wallet->id,
            )
        );
    }
}
