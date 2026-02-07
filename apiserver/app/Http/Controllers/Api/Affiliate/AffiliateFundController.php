<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Affiliate;

use App\Http\Controllers\Controller;
use App\Http\Resources\Affiliate\AffiliateFundAccountResource;
use App\Http\Resources\Affiliate\AffiliateFundTransactionResource;
use App\Models\Affiliate\AffiliateFundAccount;
use App\Models\Affiliate\AffiliateFundTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AffiliateFundController extends Controller
{
    public function accounts(Request $request): JsonResponse
    {
        $user = $request->user();

        $accounts = AffiliateFundAccount::query()
            ->where('user_id', $user->id)
            ->orderBy('fund_type')
            ->get();

        return response()->json([
            'success' => true,
            'data' => AffiliateFundAccountResource::collection($accounts),
        ]);
    }

    public function transactions(Request $request, string $fundType): JsonResponse
    {
        $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:5', 'max:50'],
        ]);

        $user = $request->user();

        $account = AffiliateFundAccount::query()
            ->where('user_id', $user->id)
            ->where('fund_type', $fundType)
            ->first();

        if (! $account) {
            return response()->json([
                'success' => false,
                'message' => 'Fund account not found',
            ], 404);
        }

        $transactions = AffiliateFundTransaction::query()
            ->where('fund_account_id', $account->id)
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => AffiliateFundTransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }
}
