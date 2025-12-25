<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\MoneyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CheckoutController - Provides checkout data for frontend
 *
 * Endpoints:
 * - GET /api/checkout/{transaction} - Get transaction details for checkout
 * - GET /api/checkout/{transaction}/status - Check payment status
 */
final class CheckoutController extends Controller
{
    /**
     * Get checkout data for transaction
     *
     * Returns transaction details needed for frontend checkout page
     */
    public function show(Request $request, Transaction $transaction): JsonResponse
    {
        // Load relationships
        $transaction->load(['integration', 'transactionable']);

        // Check if transaction is still valid
        if ($transaction->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'This transaction has already been completed',
                'transaction' => [
                    'uuid' => $transaction->uuid,
                    'status' => $transaction->status->value,
                ],
            ], 400);
        }

        if ($transaction->expires_at && $transaction->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'This transaction has expired',
                'transaction' => [
                    'uuid' => $transaction->uuid,
                    'expired_at' => $transaction->expires_at,
                ],
            ], 400);
        }

        // Get payment session ID from checkout_url (temporary storage)
        $paymentSessionId = $transaction->checkout_url;

        // Get redirect URLs from metadata
        $metadata = $transaction->metadata ?? [];
        $successUrl = $metadata['redirect_success_url'] ?? config('app.client_url').'/payment/success';
        $failureUrl = $metadata['redirect_failure_url'] ?? config('app.client_url').'/payment/failed';

        return response()->json([
            'success' => true,
            'data' => [
                'transaction' => [
                    'uuid' => $transaction->uuid,
                    'amount' => $transaction->amount,
                    'amount_formatted' => MoneyService::format($transaction->amount),
                    'amount_in_rupees' => MoneyService::toRupees($transaction->amount),
                    'purpose' => $transaction->purpose,
                    'description' => $transaction->description,
                    'status' => $transaction->status->value,
                    'type' => $transaction->type->value,
                    'expires_at' => $transaction->expires_at,
                ],
                'payment' => [
                    'provider' => $transaction->integration?->name ?? 'Cashfree',
                    'provider_slug' => $transaction->integration?->slug ?? 'cashfree',
                    'payment_session_id' => $paymentSessionId, // ⭐ CRITICAL for Cashfree SDK
                    'is_sandbox' => $transaction->integration?->is_sandbox ?? true,
                ],
                'customer' => $metadata['customer'] ?? null,
                'redirect' => [
                    'success_url' => $successUrl,
                    'failure_url' => $failureUrl,
                ],
            ],
        ]);
    }

    /**
     * Check payment status
     *
     * Frontend can poll this to check if payment completed
     */
    public function status(Request $request, Transaction $transaction): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'transaction_id' => $transaction->uuid,
                'status' => $transaction->status->value,
                'is_verified' => $transaction->is_verified,
                'verified_at' => $transaction->verified_at,
                'is_expired' => $transaction->expires_at && $transaction->expires_at->isPast(),
            ],
        ]);
    }
}
