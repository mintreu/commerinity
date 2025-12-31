<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Casts\TransactionStatusCast;
use App\Events\PaymentCompleted;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\MoneyService;
use App\Services\Payment\Providers\CashfreePaymentProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * CheckoutController - Provides checkout data for frontend
 *
 * Endpoints:
 * - GET /api/checkout/{transaction} - Get transaction details for checkout
 * - GET /api/checkout/{transaction}/status - Check payment status (with Cashfree verification)
 * - POST /api/checkout/{transaction}/verify - Force verify payment with Cashfree API
 */
final class CheckoutController extends Controller
{
    public function __construct(
        private readonly CashfreePaymentProvider $cashfreeProvider,
    ) {}

    /**
     * Get checkout data for transaction
     *
     * Returns transaction details needed for frontend checkout page
     */
    public function show(Request $request, Transaction $transaction): JsonResponse
    {
        // Load relationships
        $transaction->load(['integration', 'transactionable']);

        // Check if transaction is already completed
        if ($transaction->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'This transaction has already been completed',
                'data' => [
                    'transaction' => [
                        'uuid' => $transaction->uuid,
                        'status' => $transaction->status->value,
                        'is_verified' => true,
                    ],
                ],
            ], 400);
        }

        if ($transaction->expires_at && $transaction->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'This transaction has expired',
                'data' => [
                    'transaction' => [
                        'uuid' => $transaction->uuid,
                        'expired_at' => $transaction->expires_at,
                    ],
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
                    'is_verified' => $transaction->is_verified,
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
     * Frontend can poll this to check if payment completed.
     * Also verifies with Cashfree API if webhook was missed.
     */
    public function status(Request $request, Transaction $transaction): JsonResponse
    {
        // If already verified locally, return success
        if ($transaction->is_verified) {
            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->uuid,
                    'status' => $transaction->status->value,
                    'is_verified' => true,
                    'verified_at' => $transaction->verified_at,
                    'is_expired' => false,
                ],
            ]);
        }

        // Check if expired
        if ($transaction->expires_at && $transaction->expires_at->isPast()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->uuid,
                    'status' => $transaction->status->value,
                    'is_verified' => false,
                    'is_expired' => true,
                ],
            ]);
        }

        // ⭐ VERIFY WITH CASHFREE API (for fallback when webhook missed)
        $this->verifyWithCashfree($transaction);

        // Reload transaction after verification attempt
        $transaction->refresh();

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

    /**
     * Force verify payment with Cashfree API
     *
     * Frontend can call this after user returns from Cashfree
     * to ensure payment is recorded even if webhook failed.
     */
    public function verify(Request $request, Transaction $transaction): JsonResponse
    {
        $result = $this->verifyWithCashfree($transaction);

        $transaction->refresh();

        return response()->json([
            'success' => $result['verified'],
            'data' => [
                'transaction_id' => $transaction->uuid,
                'status' => $transaction->status->value,
                'is_verified' => $transaction->is_verified,
                'verified_at' => $transaction->verified_at,
                'message' => $result['message'] ?? null,
            ],
        ]);
    }

    /**
     * Verify payment status with Cashfree API
     *
     * This is the key method for handling payments without reliable webhooks.
     */
    private function verifyWithCashfree(Transaction $transaction): array
    {
        // Only verify if not already verified
        if ($transaction->is_verified) {
            return ['verified' => true, 'message' => 'Already verified'];
        }

        // Get provider order ID
        $providerOrderId = $transaction->provider_order_id;
        if (! $providerOrderId) {
            Log::warning('Cannot verify transaction - no provider order ID', [
                'transaction_id' => $transaction->uuid,
            ]);

            return ['verified' => false, 'message' => 'No provider order ID'];
        }

        // Call Cashfree API to verify payment status
        $verifyResponse = $this->cashfreeProvider->verify(
            new \App\Services\Payment\DTOs\PaymentVerifyRequest(
                orderId: $providerOrderId,
                amountInPaisa: $transaction->amount,
            )
        );

        Log::info('Cashfree verification result', [
            'transaction_id' => $transaction->uuid,
            'provider_order_id' => $providerOrderId,
            'status' => $verifyResponse->status,
            'success' => $verifyResponse->isCompleted(),
        ]);

        // If payment is successful, update transaction and fire event
        if ($verifyResponse->isCompleted()) {
            $transaction->update([
                'status' => TransactionStatusCast::COMPLETED,
                'is_verified' => true,
                'verified_at' => now(),
                'provider_transaction_id' => $verifyResponse->providerTransactionId,
                'provider_response' => $verifyResponse->metadata,
            ]);

            // Fire event for listeners
            event(new PaymentCompleted($transaction));

            Log::info('Payment verified and completed via API', [
                'transaction_id' => $transaction->uuid,
                'provider_transaction_id' => $verifyResponse->providerTransactionId,
            ]);

            return ['verified' => true, 'message' => 'Payment verified successfully'];
        }

        // If payment failed
        if ($verifyResponse->isFailed()) {
            $transaction->update([
                'status' => TransactionStatusCast::FAILED,
                'provider_response' => $verifyResponse->metadata,
            ]);

            return ['verified' => false, 'message' => 'Payment failed: '.$verifyResponse->message];
        }

        // Payment still pending
        return ['verified' => false, 'message' => 'Payment is still pending'];
    }
}
