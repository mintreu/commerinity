<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Webhooks;

use App\Events\PaymentCompleted;
use App\Events\PaymentFailed;
use App\Events\PayoutCompleted;
use App\Events\PayoutFailed;
use App\Events\RefundProcessed;
use App\Casts\IntegrationTypeCast;
use App\Models\Integration;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * RazorpayWebhookController - Handles Razorpay webhook events
 *
 * Payment Events:
 * - payment.authorized
 * - payment.captured
 * - payment.failed
 *
 * Refund Events:
 * - refund.created
 * - refund.processed
 * - refund.failed
 *
 * Order Events:
 * - order.paid
 *
 * Payout Events (RazorpayX):
 * - payout.processed
 * - payout.reversed
 * - payout.failed
 *
 * @see https://razorpay.com/docs/webhooks/
 */
final class RazorpayWebhookController
{
    /**
     * Handle incoming Razorpay webhook
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Verify webhook signature
        if (! $this->verifySignature($request)) {
            Log::warning('Razorpay webhook: Invalid signature', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // 2. Parse event type
        $eventType = $request->input('event');

        Log::info('Razorpay webhook received', [
            'event' => $eventType,
            'payload' => $request->input('payload'),
        ]);

        // 3. Route to appropriate handler
        try {
            match ($eventType) {
                // Payment events
                'payment.authorized' => $this->handlePaymentAuthorized($request),
                'payment.captured' => $this->handlePaymentCaptured($request),
                'payment.failed' => $this->handlePaymentFailed($request),
                'order.paid' => $this->handleOrderPaid($request),

                // Refund events
                'refund.created' => $this->handleRefundCreated($request),
                'refund.processed' => $this->handleRefundProcessed($request),
                'refund.failed' => $this->handleRefundFailed($request),

                // Payout events (RazorpayX)
                'payout.processed' => $this->handlePayoutProcessed($request),
                'payout.reversed' => $this->handlePayoutReversed($request),
                'payout.failed' => $this->handlePayoutFailed($request),

                default => Log::info('Unhandled Razorpay webhook', ['event' => $eventType]),
            };
        } catch (\Exception $e) {
            Log::error('Razorpay webhook handler exception', [
                'event' => $eventType,
                'error' => $e->getMessage(),
            ]);

            // Return 200 to prevent retries
            return response()->json(['status' => 'error', 'message' => 'Handler failed']);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Verify webhook signature
     */
    private function verifySignature(Request $request): bool
    {
        // First try payment integration
        $integration = Integration::query()
            ->bySlug('razorpay')
            ->ofType(IntegrationTypeCast::PAYMENT->value)
            ->active()
            ->first();

        // Fall back to payout integration
        if (! $integration) {
            $integration = Integration::query()
                ->bySlug('razorpay')
                ->ofType(IntegrationTypeCast::PAYOUT->value)
                ->active()
                ->first();
        }

        if (! $integration) {
            Log::warning('Razorpay integration not found');

            return false;
        }

        $webhookSecret = $integration->getWebhookSecret();
        if (! $webhookSecret) {
            // Allow in development
            if (app()->environment('local', 'testing')) {
                return true;
            }
            Log::warning('Razorpay webhook secret not configured');

            return false;
        }

        $signature = $request->header('x-razorpay-signature');
        $rawBody = $request->getContent();

        if (! $signature) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $rawBody, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Handle payment authorized (before capture)
     */
    private function handlePaymentAuthorized(Request $request): void
    {
        $payment = $request->input('payload.payment.entity');
        $orderId = $payment['order_id'] ?? null;
        $notes = $payment['notes'] ?? [];
        $transactionId = $notes['transaction_id'] ?? null;

        // Find transaction by receipt or notes
        $transaction = $this->findTransaction($orderId, $transactionId, $payment['id'] ?? null);

        if (! $transaction) {
            Log::warning('Razorpay webhook: Transaction not found', [
                'order_id' => $orderId,
                'payment_id' => $payment['id'] ?? null,
            ]);

            return;
        }

        $transaction->update([
            'status' => 'processing',
            'provider_reference' => $payment['id'] ?? null,
            'metadata' => array_merge($transaction->metadata ?? [], [
                'razorpay_payment_id' => $payment['id'] ?? null,
                'authorized_at' => now()->toIso8601String(),
            ]),
        ]);

        Log::info('Razorpay payment authorized', [
            'payment_id' => $payment['id'] ?? null,
            'order_id' => $orderId,
        ]);
    }

    /**
     * Handle payment captured (completed)
     */
    private function handlePaymentCaptured(Request $request): void
    {
        $payment = $request->input('payload.payment.entity');
        $orderId = $payment['order_id'] ?? null;
        $notes = $payment['notes'] ?? [];
        $transactionId = $notes['transaction_id'] ?? null;

        $transaction = $this->findTransaction($orderId, $transactionId, $payment['id'] ?? null);

        if (! $transaction) {
            Log::warning('Razorpay webhook: Transaction not found', [
                'order_id' => $orderId,
                'payment_id' => $payment['id'] ?? null,
            ]);

            return;
        }

        if ($transaction->status === 'completed') {
            return;
        }

        $transaction->update([
            'status' => 'completed',
            'provider_reference' => $payment['id'] ?? null,
            'provider_response' => $request->all(),
            'completed_at' => now(),
        ]);

        Log::info('Razorpay payment captured', [
            'payment_id' => $payment['id'] ?? null,
            'order_id' => $orderId,
        ]);

        if (class_exists(PaymentCompleted::class)) {
            event(new PaymentCompleted($transaction));
        }
    }

    /**
     * Handle payment failed
     */
    private function handlePaymentFailed(Request $request): void
    {
        $payment = $request->input('payload.payment.entity');
        $orderId = $payment['order_id'] ?? null;
        $notes = $payment['notes'] ?? [];
        $transactionId = $notes['transaction_id'] ?? null;

        $transaction = $this->findTransaction($orderId, $transactionId, $payment['id'] ?? null);

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
            'metadata' => array_merge($transaction->metadata ?? [], [
                'error_code' => $payment['error_code'] ?? null,
                'error_description' => $payment['error_description'] ?? null,
            ]),
        ]);

        Log::info('Razorpay payment failed', [
            'payment_id' => $payment['id'] ?? null,
            'error' => $payment['error_description'] ?? null,
        ]);

        if (class_exists(PaymentFailed::class)) {
            event(new PaymentFailed($transaction));
        }
    }

    /**
     * Handle order paid (alternative to payment.captured)
     */
    private function handleOrderPaid(Request $request): void
    {
        $order = $request->input('payload.order.entity');
        $orderId = $order['id'] ?? null;
        $receipt = $order['receipt'] ?? null;

        $transaction = Transaction::where('uuid', $receipt)
            ->orWhere(function ($query) use ($orderId) {
                $query->whereJsonContains('metadata->razorpay_order_id', $orderId);
            })
            ->first();

        if (! $transaction) {
            return;
        }

        if ($transaction->status === 'completed') {
            return;
        }

        $transaction->update([
            'status' => 'completed',
            'provider_response' => $request->all(),
            'completed_at' => now(),
        ]);

        Log::info('Razorpay order paid', ['order_id' => $orderId]);

        if (class_exists(PaymentCompleted::class)) {
            event(new PaymentCompleted($transaction));
        }
    }

    /**
     * Handle refund created
     */
    private function handleRefundCreated(Request $request): void
    {
        $refund = $request->input('payload.refund.entity');
        $paymentId = $refund['payment_id'] ?? null;

        Log::info('Razorpay refund created', [
            'refund_id' => $refund['id'] ?? null,
            'payment_id' => $paymentId,
        ]);

        // Just log - wait for processed/failed
    }

    /**
     * Handle refund processed
     */
    private function handleRefundProcessed(Request $request): void
    {
        $refund = $request->input('payload.refund.entity');
        $paymentId = $refund['payment_id'] ?? null;
        $notes = $refund['notes'] ?? [];

        $transaction = Transaction::where('provider_reference', $paymentId)
            ->orWhere('uuid', $notes['transaction_id'] ?? '')
            ->first();

        if (! $transaction) {
            Log::warning('Razorpay refund webhook: Transaction not found', [
                'payment_id' => $paymentId,
            ]);

            return;
        }

        $transaction->update([
            'status' => 'refunded',
            'metadata' => array_merge($transaction->metadata ?? [], [
                'refund_id' => $refund['id'] ?? null,
                'refund_amount' => $refund['amount'] ?? null,
                'refunded_at' => now()->toIso8601String(),
            ]),
        ]);

        Log::info('Razorpay refund processed', [
            'refund_id' => $refund['id'] ?? null,
            'payment_id' => $paymentId,
        ]);

        if (class_exists(RefundProcessed::class)) {
            event(new RefundProcessed($transaction));
        }
    }

    /**
     * Handle refund failed
     */
    private function handleRefundFailed(Request $request): void
    {
        $refund = $request->input('payload.refund.entity');

        Log::warning('Razorpay refund failed', [
            'refund_id' => $refund['id'] ?? null,
            'payment_id' => $refund['payment_id'] ?? null,
        ]);

        // Refund failure doesn't change transaction status
        // Admin should be notified to handle manually
    }

    /**
     * Handle payout processed (RazorpayX)
     */
    private function handlePayoutProcessed(Request $request): void
    {
        $payout = $request->input('payload.payout.entity');
        $referenceId = $payout['reference_id'] ?? null;
        $payoutId = $payout['id'] ?? null;

        $transaction = Transaction::where('uuid', $referenceId)
            ->orWhere('provider_reference', $payoutId)
            ->first();

        if (! $transaction) {
            Log::warning('RazorpayX payout webhook: Transaction not found', [
                'reference_id' => $referenceId,
                'payout_id' => $payoutId,
            ]);

            return;
        }

        if ($transaction->status === 'completed') {
            return;
        }

        $transaction->update([
            'status' => 'completed',
            'provider_reference' => $payoutId,
            'provider_response' => $request->all(),
            'completed_at' => now(),
            'metadata' => array_merge($transaction->metadata ?? [], [
                'utr' => $payout['utr'] ?? null,
            ]),
        ]);

        Log::info('RazorpayX payout processed', [
            'payout_id' => $payoutId,
            'utr' => $payout['utr'] ?? null,
        ]);

        if (class_exists(PayoutCompleted::class)) {
            event(new PayoutCompleted($transaction));
        }
    }

    /**
     * Handle payout reversed (RazorpayX)
     */
    private function handlePayoutReversed(Request $request): void
    {
        $payout = $request->input('payload.payout.entity');
        $referenceId = $payout['reference_id'] ?? null;
        $payoutId = $payout['id'] ?? null;

        $transaction = Transaction::where('uuid', $referenceId)
            ->orWhere('provider_reference', $payoutId)
            ->first();

        if (! $transaction) {
            return;
        }

        $transaction->update([
            'status' => 'reversed',
            'provider_response' => $request->all(),
            'metadata' => array_merge($transaction->metadata ?? [], [
                'reversed_at' => now()->toIso8601String(),
            ]),
        ]);

        Log::info('RazorpayX payout reversed', ['payout_id' => $payoutId]);

        if (class_exists(PayoutFailed::class)) {
            event(new PayoutFailed($transaction));
        }
    }

    /**
     * Handle payout failed (RazorpayX)
     */
    private function handlePayoutFailed(Request $request): void
    {
        $payout = $request->input('payload.payout.entity');
        $referenceId = $payout['reference_id'] ?? null;
        $payoutId = $payout['id'] ?? null;

        $transaction = Transaction::where('uuid', $referenceId)
            ->orWhere('provider_reference', $payoutId)
            ->first();

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
            'metadata' => array_merge($transaction->metadata ?? [], [
                'failure_reason' => $payout['failure_reason'] ?? null,
            ]),
        ]);

        Log::info('RazorpayX payout failed', [
            'payout_id' => $payoutId,
            'reason' => $payout['failure_reason'] ?? null,
        ]);

        if (class_exists(PayoutFailed::class)) {
            event(new PayoutFailed($transaction));
        }
    }

    /**
     * Find transaction by various identifiers
     */
    private function findTransaction(?string $orderId, ?string $transactionId, ?string $paymentId): ?Transaction
    {
        if ($transactionId) {
            $transaction = Transaction::where('uuid', $transactionId)->first();
            if ($transaction) {
                return $transaction;
            }
        }

        if ($paymentId) {
            $transaction = Transaction::where('provider_reference', $paymentId)->first();
            if ($transaction) {
                return $transaction;
            }
        }

        if ($orderId) {
            $transaction = Transaction::whereJsonContains('metadata->razorpay_order_id', $orderId)->first();
            if ($transaction) {
                return $transaction;
            }
        }

        return null;
    }
}
