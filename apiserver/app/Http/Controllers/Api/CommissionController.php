<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Casts\CommissionStatusCast;
use App\Http\Controllers\Controller;
use App\Models\Mlm\MlmCommission;
use App\Services\MoneyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CommissionController - API for commission/earnings management
 *
 * Provides:
 * - View earnings summary and statistics
 * - View commission history with filters
 * - View earnings by type breakdown
 */
final class CommissionController extends Controller
{
    /**
     * Get earnings summary for the authenticated user.
     *
     * GET /api/commissions/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get total earnings (all time)
        $totalEarnings = MlmCommission::where('user_id', $user->id)
            ->where('status', CommissionStatusCast::PAID)
            ->sum('net_amount');

        // Get pending earnings
        $pendingEarnings = MlmCommission::where('user_id', $user->id)
            ->whereIn('status', [CommissionStatusCast::PENDING, CommissionStatusCast::APPROVED])
            ->sum('net_amount');

        // Get this month's earnings
        $thisMonthEarnings = MlmCommission::where('user_id', $user->id)
            ->where('status', CommissionStatusCast::PAID)
            ->where('period_key', now()->format('Y-m'))
            ->sum('net_amount');

        // Get last month's earnings
        $lastMonthEarnings = MlmCommission::where('user_id', $user->id)
            ->where('status', CommissionStatusCast::PAID)
            ->where('period_key', now()->subMonth()->format('Y-m'))
            ->sum('net_amount');

        // Calculate growth
        $growth = $lastMonthEarnings > 0
            ? round((($thisMonthEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100, 1)
            : ($thisMonthEarnings > 0 ? 100 : 0);

        // Get commission count
        $totalCommissions = MlmCommission::where('user_id', $user->id)
            ->where('status', CommissionStatusCast::PAID)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_earnings' => $totalEarnings,
                'total_earnings_formatted' => MoneyService::format($totalEarnings),
                'pending_earnings' => $pendingEarnings,
                'pending_earnings_formatted' => MoneyService::format($pendingEarnings),
                'this_month_earnings' => $thisMonthEarnings,
                'this_month_earnings_formatted' => MoneyService::format($thisMonthEarnings),
                'last_month_earnings' => $lastMonthEarnings,
                'last_month_earnings_formatted' => MoneyService::format($lastMonthEarnings),
                'growth_percent' => $growth,
                'total_commissions' => $totalCommissions,
            ],
        ]);
    }

    /**
     * Get commission history for the authenticated user.
     *
     * GET /api/commissions
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['sometimes', 'string', 'in:pending,approved,processing,paid,held,cancelled,reversed'],
            'type' => ['sometimes', 'string'],
            'period' => ['sometimes', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:50'],
        ]);

        $user = $request->user();

        $query = MlmCommission::where('user_id', $user->id)
            ->with(['fromUser:id,uuid,name'])
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by period
        if ($request->filled('period')) {
            $query->where('period_key', $request->input('period'));
        }

        $commissions = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $commissions->map(fn ($commission) => $this->formatCommission($commission)),
            'meta' => [
                'current_page' => $commissions->currentPage(),
                'last_page' => $commissions->lastPage(),
                'per_page' => $commissions->perPage(),
                'total' => $commissions->total(),
            ],
        ]);
    }

    /**
     * Get single commission details.
     *
     * GET /api/commissions/{uuid}
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();

        $commission = MlmCommission::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->with(['fromUser:id,uuid,name,email', 'paidViaTransaction'])
            ->first();

        if (! $commission) {
            return response()->json([
                'success' => false,
                'message' => 'Commission not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatCommission($commission, detailed: true),
        ]);
    }

    /**
     * Get earnings breakdown by type.
     *
     * GET /api/commissions/by-type
     */
    public function byType(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'string', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $user = $request->user();

        $query = MlmCommission::where('user_id', $user->id)
            ->where('status', CommissionStatusCast::PAID);

        if ($request->filled('period')) {
            $query->where('period_key', $request->input('period'));
        }

        $breakdown = $query->selectRaw('type, SUM(net_amount) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->map(fn ($item) => [
                'type' => $item->type->value,
                'type_label' => $item->type->getLabel(),
                'total' => (int) $item->total,
                'total_formatted' => MoneyService::format((int) $item->total),
                'count' => $item->count,
            ]);

        return response()->json([
            'success' => true,
            'data' => $breakdown,
        ]);
    }

    /**
     * Get monthly earnings history.
     *
     * GET /api/commissions/monthly
     */
    public function monthly(Request $request): JsonResponse
    {
        $request->validate([
            'months' => ['sometimes', 'integer', 'min:1', 'max:24'],
        ]);

        $user = $request->user();
        $months = $request->input('months', 12);

        $earnings = MlmCommission::where('user_id', $user->id)
            ->where('status', CommissionStatusCast::PAID)
            ->where('commission_date', '>=', now()->subMonths($months)->startOfMonth())
            ->selectRaw('period_key, SUM(net_amount) as total, COUNT(*) as count')
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get()
            ->map(fn ($item) => [
                'period' => $item->period_key,
                'total' => (int) $item->total,
                'total_formatted' => MoneyService::format((int) $item->total),
                'count' => $item->count,
            ]);

        return response()->json([
            'success' => true,
            'data' => $earnings,
        ]);
    }

    /**
     * Format commission for API response.
     */
    private function formatCommission(MlmCommission $commission, bool $detailed = false): array
    {
        $data = [
            'uuid' => $commission->uuid,
            'type' => $commission->type->value,
            'type_label' => $commission->type->getLabel(),
            'level' => $commission->level,
            'status' => $commission->status->value,
            'status_label' => $commission->status->getLabel(),
            'gross_amount' => $commission->gross_amount,
            'gross_amount_formatted' => MoneyService::format($commission->gross_amount),
            'tds_amount' => $commission->tds_amount,
            'tds_amount_formatted' => MoneyService::format($commission->tds_amount),
            'admin_fee' => $commission->admin_fee,
            'admin_fee_formatted' => MoneyService::format($commission->admin_fee),
            'net_amount' => $commission->net_amount,
            'net_amount_formatted' => MoneyService::format($commission->net_amount),
            'description' => $commission->description,
            'from_user' => $commission->fromUser ? [
                'uuid' => $commission->fromUser->uuid,
                'name' => $commission->fromUser->name,
            ] : null,
            'commission_date' => $commission->commission_date?->toDateString(),
            'period_key' => $commission->period_key,
            'paid_at' => $commission->paid_at?->toIso8601String(),
            'created_at' => $commission->created_at->toIso8601String(),
        ];

        if ($detailed) {
            $data['rate_percent'] = $commission->rate_percent;
            $data['base_amount'] = $commission->base_amount;
            $data['base_amount_formatted'] = MoneyService::format($commission->base_amount ?? 0);
            $data['metadata'] = $commission->metadata;
            $data['approved_at'] = $commission->approved_at?->toIso8601String();

            if ($commission->paidViaTransaction) {
                $data['transaction'] = [
                    'uuid' => $commission->paidViaTransaction->uuid,
                    'reference_number' => $commission->paidViaTransaction->reference_number,
                ];
            }
        }

        return $data;
    }
}
