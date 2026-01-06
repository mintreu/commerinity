<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payout\CashgramRequest;
use App\Http\Requests\Payout\PayoutToWalletRequest;
use App\Services\IntegrationServices\Payout\PayoutService;
use App\Services\IntegrationServices\Payout\Providers\CashfreePayoutProvider;
use App\Services\MoneyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PayoutController - Admin Payout Operations
 *
 * Note: User-facing features are in other controllers:
 * - Beneficiary Management → BeneficiaryAccountController (/api/wallet/beneficiaries)
 * - User Withdrawals → WalletController (/api/wallet/withdraw)
 *
 * Admin-Only Endpoints:
 * - POST /api/payouts/to-wallet - Credit user wallet (commissions, affiliate, refunds)
 * - POST /api/payouts/cashgram - Create Cashgram (payout link)
 * - GET /api/payouts/cashgram/{id}/status - Get Cashgram status
 * - GET /api/payouts/balance - Get Cashfree payout balance
 */
final class PayoutController extends Controller
{
    public function __construct(
        private readonly PayoutService $payoutService,
        private readonly CashfreePayoutProvider $cashfreePayout,
    ) {}

    // ========================================
    // ADMIN: PAYOUT TO WALLET
    // ========================================

    /**
     * Credit user wallet (Admin only - for commissions, affiliate payouts, etc.)
     */
    public function payoutToWallet(PayoutToWalletRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->payoutService->creditWalletByUserId(
            userId: $validated['user_id'],
            amountInPaisa: $validated['amount'],
            type: $validated['type'],
            description: $validated['description'] ?? null,
            referenceId: $validated['reference_id'] ?? null,
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'transaction_id' => $result['transaction_id'],
                    'amount_formatted' => MoneyService::format($result['amount']),
                    'wallet_balance_formatted' => MoneyService::format($result['wallet_balance']),
                ],
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 422);
    }

    // ========================================
    // CASHGRAM (Admin/Distributor Feature)
    // ========================================

    /**
     * Create Cashgram (payout link)
     */
    public function createCashgram(CashgramRequest $request): JsonResponse
    {
        // This is typically an admin/distributor feature
        $validated = $request->validated();

        $result = $this->cashfreePayout->createCashgram([
            'cashgram_id' => $validated['cashgram_id'] ?? null,
            'amount' => $validated['amount'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'name' => $validated['name'],
            'purpose' => $validated['purpose'] ?? 'Payout',
            'remark' => $validated['remark'] ?? null,
            'notify_customer' => $validated['notify_customer'] ?? true,
            'expire_by' => $validated['expire_by'] ?? null,
        ]);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'cashgram_id' => $result['cashgram_id'],
                    'link' => $result['link'],
                    'reference_id' => $result['reference_id'] ?? null,
                ],
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 422);
    }

    /**
     * Get Cashgram status
     */
    public function cashgramStatus(Request $request, string $cashgramId): JsonResponse
    {
        $result = $this->cashfreePayout->getCashgramStatus($cashgramId);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => [
                    'cashgram_id' => $cashgramId,
                    'status' => $result['status'],
                    'data' => $result['data'],
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 422);
    }

    // ========================================
    // UTILITY
    // ========================================

    /**
     * Get payout balance (admin)
     */
    public function getBalance(Request $request): JsonResponse
    {
        $balance = $this->cashfreePayout->getBalance();

        if ($balance) {
            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $balance['balance'] ?? 0,
                    'currency' => $balance['currency'] ?? 'INR',
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Could not retrieve balance',
        ], 500);
    }
}
