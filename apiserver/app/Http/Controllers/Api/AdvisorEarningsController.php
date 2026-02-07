<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Casts\CommissionTypeCast;
use App\Casts\OrderStatusCast;
use App\Http\Controllers\Controller;
use App\Models\Affiliate\AffiliateCommission;
use App\Models\Ecommerce\Order;
use App\Services\MoneyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdvisorEarningsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $originatedUserIds = $user?->originatedUsers()->pluck('id') ?? collect();

        $saleStatuses = [
            OrderStatusCast::CONFIRMED->value,
            OrderStatusCast::PROCESSING->value,
            OrderStatusCast::SHIPPED->value,
            OrderStatusCast::DELIVERED->value,
            OrderStatusCast::COMPLETED->value,
        ];

        $totalSaleVolume = 0;
        if ($originatedUserIds->isNotEmpty()) {
            $totalSaleVolume = (int) Order::query()
                ->whereIn('user_id', $originatedUserIds)
                ->whereIn('status', $saleStatuses)
                ->sum('total');
        }

        $advisorCommissionTypes = [
            CommissionTypeCast::ORIGINATOR_JOINING->value,
            CommissionTypeCast::ORIGINATOR_RECURRING->value,
            CommissionTypeCast::AGENT_SALARY->value,
        ];

        $totalEarnings = (int) AffiliateCommission::query()
            ->where('user_id', $user->id)
            ->whereIn('type', $advisorCommissionTypes)
            ->sum('net_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_sale_volume' => $totalSaleVolume,
                'total_sale_volume_formatted' => MoneyService::format($totalSaleVolume),
                'total_earnings' => $totalEarnings,
                'total_earnings_formatted' => MoneyService::format($totalEarnings),
            ],
        ]);
    }
}
