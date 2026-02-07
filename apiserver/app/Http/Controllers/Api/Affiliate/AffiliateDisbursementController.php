<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Affiliate;

use App\Http\Controllers\Controller;
use App\Http\Resources\Affiliate\AffiliatePayoutResource;
use App\Models\Affiliate\AffiliatePayout;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AffiliateDisbursementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);

        $payouts = AffiliatePayout::query()
            ->where('user_id', $user->id)
            ->orderByDesc('period_end')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => AffiliatePayoutResource::collection($payouts),
        ]);
    }

    public function show(Request $request, AffiliatePayout $payout): JsonResponse
    {
        $user = $request->user();
        if ($payout->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => AffiliatePayoutResource::make($payout),
        ]);
    }

    public function invoice(Request $request, AffiliatePayout $payout)
    {
        $user = $request->user();
        if ($payout->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $pdf = Pdf::loadView('invoices.affiliate_payout_invoice', [
            'payout' => $payout,
        ])->setPaper('a4')->setWarnings(false);

        return $pdf->stream('affiliate-payout-'.$payout->uuid.'.pdf');
    }
}
