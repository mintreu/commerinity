<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Webhooks;

use App\Casts\TransactionStatusCast;
use App\Events\PaymentCompleted;
use App\Events\PaymentFailed;
use App\Events\PayoutCompleted;
use App\Events\PayoutFailed;
use App\Events\RefundProcessed;
use App\Models\Integration;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * CashfreeWebhookController - Handles Cashfree webhook events
 *
 * Payment Events:
 * - PAYMENT_SUCCESS_WEBHOOK
 * - PAYMENT_FAILED_WEBHOOK
 * - PAYMENT_USER_DROPPED_WEBHOOK
 *
 * Refund Events:
 * - REFUND_STATUS_WEBHOOK
 *
 * Payout Events (from Cashfree Payouts):
 * - TRANSFER_SUCCESS
 * - TRANSFER_FAILED
 * - TRANSFER_REVERSED
 *
 * @see https://docs.cashfree.com/reference/webhooks-1
 */
final class CashfreeWebhookController
{
    /**
     * Handle incoming Cashfree webhook
     *
     * CRITICAL: Cashfree requires plain text "OK" response with HTTP 200.
     * JSON responses will cause webhook verification to fail.
     */
    public function handle(Request $request): Response
    {
        // 1. Verify webhook signature
        if (! $this->verifySignature($request)) {
            Log::warning('Cashfree webhook: Invalid signature', [
                'ip' => $request->ip(),
            ]);

            // Still return 200 to prevent retries on invalid signature
            return response('OK', 200);
        }

        // 2. Parse event type
        $eventType = $request->input('type') ?? $request->input('event');

        Log::info('Cashfree webhook received', [
            'type' => $eventType,
            'data' => $request->except(['secret']),
        ]);

        // 3. Route to appropriate handler
        try {
            match ($eventType) {
                'PAYMENT_SUCCESS_WEBHOOK' => $this->handlePaymentSuccess($request),
                'PAYMENT_FAILED_WEBHOOK' => $this->handlePaymentFailed($request),
                'PAYMENT_USER_DROPPED_WEBHOOK' => $this->handlePaymentDropped($request),
                'REFUND_STATUS_WEBHOOK' => $this->handleRefund($request),
                'TRANSFER_SUCCESS' => $this->handlePayoutSuccess($request),
                'TRANSFER_FAILED' => $this->handlePayoutFailed($request),
                'TRANSFER_REVERSED' => $this->handlePayoutReversed($request),
                default => Log::info('Unhandled Cashfree webhook', ['type' => $eventType]),
            };
        } catch (\Exception $e) {
            Log::error('Cashfree webhook handler exception', [
                'type' => $eventType,
                'error' => $e->getMessage(),
            ]);

            // Return 200 anyway to prevent retries (Cashfree requirement)
            return response('OK', 200);
        }

        // CRITICAL: Return plain text "OK", NOT JSON
        return response('OK', 200);
    }

    /**
     * Handle payment payout webhook (separate endpoint for Cashfree Payouts)
     */
    public function handlePayout(Request $request): JsonResponse
    {
        if (! $this->verifyPayoutSignature($request)) {
            Log::warning('Cashfree payout webhook: Invalid signature');

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $eventType = $request->input('event');

        Log::info('Cashfree payout webhook received', [
            'event' => $eventType,
            'data' => $request->except(['secret']),
        ]);

        try {
            match ($eventType) {
                'TRANSFER_SUCCESS' => $this->handlePayoutSuccess($request),
                'TRANSFER_FAILED' => $this->handlePayoutFailed($request),
                'TRANSFER_REVERSED' => $this->handlePayoutReversed($request),
                'TRANSFER_ACKNOWLEDGED' => $this->handlePayoutAcknowledged($request),
                default => Log::info('Unhandled Cashfree payout webhook', ['event' => $eventType]),
            };
        } catch (\Exception $e) {
            Log::error('Cashfree payout webhook exception', [
                'event' => $eventType,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Verify payment webhook signature
     */
    private function verifySignature(Request $request): bool
    {
        $integration = Integration::query()
            ->bySlug('cashfree')
            ->ofType(Integration::TYPE_PAYMENT)
            ->active()
            ->first();

        if (! $integration) {
            Log::warning('Cashfree payment integration not found');

            return false;
        }

        $webhookSecret = $integration->getWebhookSecret();
        if (! $webhookSecret) {
            // Allow in development if no secret configured
            if (app()->environment('local', 'testing')) {
                return true;
            }
            Log::warning('Cashfree webhook secret not configured');

            return false;
        }

        $timestamp = $request->header('x-webhook-timestamp');
        $signature = $request->header('x-webhook-signature');
        $rawBody = $request->getContent();

        if (! $timestamp || ! $signature) {
            return false;
        }

        $computedSignature = base64_encode(
            hash_hmac('sha256', $timestamp.$rawBody, $webhookSecret, true)
        );

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Verify payout webhook signature
     */
    private function verifyPayoutSignature(Request $request): bool
    {
        $integration = Integration::query()
            ->bySlug('cashfree')
            ->ofType(Integration::TYPE_PAYOUT)
            ->active()
            ->first();

        if (! $integration) {
            Log::warning('Cashfree payout integration not found');

            return false;
        }

        $webhookSecret = $integration->getWebhookSecret();
        if (! $webhookSecret) {
            if (app()->environment('local', 'testing')) {
                return true;
            }

            return false;
        }

        // Cashfree Payouts uses different signature format
        $signature = $request->header('x-webhook-signature');
        $rawBody = $request->getContent();

        if (! $signature) {
            return false;
        }

        $computedSignature = hash_hmac('sha256', $rawBody, $webhookSecret);

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentSuccess(Request $request): void
    {
        $orderId = $request->input('data.order.order_id');
        $transaction = Transaction::where('uuid', $orderId)->first();

        if (! $transaction) {
            Log::error('Cashfree webhook: Transaction not found', ['order_id' => $orderId]);

            return;
        }

        // Prevent duplicate processing
        if ($transaction->status === TransactionStatusCast::COMPLETED) {
            Log::info('Cashfree webhook: Transaction already completed', ['order_id' => $orderId]);

            return;
        }

        $transaction->update([
            'status' => TransactionStatusCast::COMPLETED,
            'provider_reference' => $request->input('data.payment.cf_payment_id'),
            'provider_response' => $request->all(),
            'completed_at' => now(),
        ]);

        Log::info('Cashfree payment confirmed', [
            'order_id' => $orderId,
            'cf_payment_id' => $request->input('data.payment.cf_payment_id'),
        ]);

        // Dispatch event for listeners
        if (class_exists(PaymentCompleted::class)) {
            event(new PaymentCompleted($transaction));
        }
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentFailed(Request $request): void
    {
        $orderId = $request->input('data.order.order_id');
        $transaction = Transaction::where('uuid', $orderId)->first();

        if (! $transaction) {
            Log::error('Cashfree webhook: Transaction not found', ['order_id' => $orderId]);

            return;
        }

        if (in_array($transaction->status, [TransactionStatusCast::COMPLETED, TransactionStatusCast::FAILED])) {
            return;
        }

        $transaction->update([
            'status' => TransactionStatusCast::FAILED,
            'provider_response' => $request->all(),
            'failed_at' => now(),
        ]);

        Log::info('Cashfree payment failed', ['order_id' => $orderId]);

        if (class_exists(PaymentFailed::class)) {
            event(new PaymentFailed($transaction));
        }
    }

    /**
     * Handle user dropped payment
     */
    private function handlePaymentDropped(Request $request): void
    {
        $orderId = $request->input('data.order.order_id');
        $transaction = Transaction::where('uuid', $orderId)->first();

        if (! $transaction) {
            return;
        }

        if ($transaction->status !== TransactionStatusCast::PENDING) {
            return;
        }

        $transaction->update([
            'status' => TransactionStatusCast::CANCELLED,
            'provider_response' => $request->all(),
        ]);

        Log::info('Cashfree payment dropped by user', ['order_id' => $orderId]);
    }

    /**
     * Handle refund status update
     */
    private function handleRefund(Request $request): void
    {
        $orderId = $request->input('data.refund.order_id');
        $refundStatus = $request->input('data.refund.refund_status');

        $transaction = Transaction::where('uuid', $orderId)->first();

        if (! $transaction) {
            Log::error('Cashfree refund webhook: Transaction not found', ['order_id' => $orderId]);

            return;
        }

        if ($refundStatus === 'SUCCESS') {
            $transaction->update([
                'status' => TransactionStatusCast::REFUNDED,
                'metadata' => array_merge(
                    $transaction->metadata ?? [],
                    ['refund' => $request->all()]
                ),
                'refunded_at' => now(),
            ]);

            Log::info('Cashfree refund completed', [
                'order_id' => $orderId,
                'refund_id' => $request->input('data.refund.cf_refund_id'),
            ]);

            if (class_exists(RefundProcessed::class)) {
                event(new RefundProcessed($transaction));
            }
        }
    }

    /**
     * Handle successful payout
     */
    private function handlePayoutSuccess(Request $request): void
    {
        $transferId = $request->input('transferId') ?? $request->input('data.transferId');
        $transaction = Transaction::where('uuid', $transferId)->first();

        if (! $transaction) {
            Log::error('Cashfree payout webhook: Transaction not found', ['transfer_id' => $transferId]);

            return;
        }

        if ($transaction->status === 'completed') {
            return;
        }

        $transaction->update([
            'status' => 'completed',
            'provider_reference' => $request->input('referenceId') ?? $request->input('data.referenceId'),
            'provider_response' => $request->all(),
            'completed_at' => now(),
            'metadata' => array_merge(
                $transaction->metadata ?? [],
                ['utr' => $request->input('utr') ?? $request->input('data.utr')]
            ),
        ]);

        Log::info('Cashfree payout completed', [
            'transfer_id' => $transferId,
            'utr' => $request->input('utr') ?? $request->input('data.utr'),
        ]);

        // Release hold and process
        if (class_exists(PayoutCompleted::class)) {
            event(new PayoutCompleted($transaction));
        }
    }

    /**
     * Handle failed payout
     */
    private function handlePayoutFailed(Request $request): void
    {
        $transferId = $request->input('transferId') ?? $request->input('data.transferId');
        $transaction = Transaction::where('uuid', $transferId)->first();

        if (! $transaction) {
            return;
        }

        if (in_array($transaction->status, ['completed', 'failed'])) {
            return;
        }

        $transaction->update([
            'status' => 'failed',
            'provider_response' => $request->all(),
            'failed_at' => now(),
            'metadata' => array_merge(
                $transaction->metadata ?? [],
                ['failure_reason' => $request->input('reason') ?? $request->input('data.reason')]
            ),
        ]);

        Log::info('Cashfree payout failed', ['transfer_id' => $transferId]);

        if (class_exists(PayoutFailed::class)) {
            event(new PayoutFailed($transaction));
        }
    }

    /**
     * Handle reversed payout
     */
    private function handlePayoutReversed(Request $request): void
    {
        $transferId = $request->input('transferId') ?? $request->input('data.transferId');
        $transaction = Transaction::where('uuid', $transferId)->first();

        if (! $transaction) {
            return;
        }

        $transaction->update([
            'status' => 'reversed',
            'provider_response' => $request->all(),
            'metadata' => array_merge(
                $transaction->metadata ?? [],
                ['reversed_at' => now()->toIso8601String()]
            ),
        ]);

        Log::info('Cashfree payout reversed', ['transfer_id' => $transferId]);

        if (class_exists(PayoutFailed::class)) {
            event(new PayoutFailed($transaction));
        }
    }

    /**
     * Handle acknowledged payout (intermediate state)
     */
    private function handlePayoutAcknowledged(Request $request): void
    {
        $transferId = $request->input('transferId') ?? $request->input('data.transferId');

        Log::info('Cashfree payout acknowledged', ['transfer_id' => $transferId]);

        // No status update needed - wait for SUCCESS/FAILED
    }
}
