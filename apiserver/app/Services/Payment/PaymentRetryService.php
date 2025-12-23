<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Casts\TransactionStatusCast;
use App\Models\Transaction;
use App\Services\Payment\Contracts\PaymentProviderInterface;
use App\Services\Payment\DTOs\PaymentInitiateRequest;
use App\Services\Payment\DTOs\PaymentResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PaymentRetryService - Handles payment retry with expiry check and rate limiting
 *
 * Features:
 * - Check if existing payment order is expired
 * - Cancel expired orders at provider before creating new one
 * - Rate limiting to prevent abuse
 * - Track retry attempts per transaction
 */
final class PaymentRetryService
{
    /**
     * Rate limit: max retries per transaction per hour
     */
    private const MAX_RETRIES_PER_HOUR = 5;

    /**
     * Rate limit: max retries per user per hour (across all transactions)
     */
    private const MAX_USER_RETRIES_PER_HOUR = 10;

    /**
     * Cooldown period between retries in seconds
     */
    private const RETRY_COOLDOWN_SECONDS = 30;

    /**
     * Default expiry time for provider orders in minutes
     */
    private const DEFAULT_ORDER_EXPIRY_MINUTES = 30;

    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * Retry payment for a transaction
     *
     * @param  Transaction  $transaction  The transaction to retry
     * @param  PaymentInitiateRequest  $request  New payment request details
     * @param  string|null  $providerSlug  Specific provider to use (optional)
     */
    public function retryPayment(
        Transaction $transaction,
        PaymentInitiateRequest $request,
        ?string $providerSlug = null
    ): PaymentResponse {
        // 1. Validate transaction can be retried
        $validation = $this->validateRetryEligibility($transaction);
        if (! $validation['eligible']) {
            return PaymentResponse::failed($validation['reason']);
        }

        // 2. Check rate limits
        $rateLimitCheck = $this->checkRateLimits($transaction);
        if (! $rateLimitCheck['allowed']) {
            return PaymentResponse::failed($rateLimitCheck['reason']);
        }

        // 3. Get the provider
        $provider = $providerSlug
            ? $this->paymentService->getPaymentProvider($providerSlug)
            : $this->paymentService->getDefaultPaymentProvider();

        if (! $provider || ! $provider->isAvailable()) {
            return PaymentResponse::failed('Payment provider not available');
        }

        try {
            return DB::transaction(function () use ($transaction, $request, $provider) {
                // 4. Cancel existing provider order if expired
                $this->handleExpiredProviderOrder($transaction, $provider);

                // 5. Update transaction for retry
                $this->prepareTransactionForRetry($transaction);

                // 6. Record retry attempt
                $this->recordRetryAttempt($transaction);

                // 7. Initiate new payment
                $response = $provider->initiate($request);

                // 8. Update transaction with new provider details
                if ($response->success) {
                    $this->updateTransactionWithNewOrder($transaction, $response);
                }

                Log::info('Payment retry attempted', [
                    'transaction_uuid' => $transaction->uuid,
                    'provider' => $provider->getSlug(),
                    'success' => $response->success,
                    'new_order_id' => $response->providerOrderId,
                ]);

                return $response;
            });
        } catch (\Exception $e) {
            Log::error('Payment retry failed', [
                'transaction_uuid' => $transaction->uuid,
                'error' => $e->getMessage(),
            ]);

            return PaymentResponse::failed('Failed to retry payment: '.$e->getMessage());
        }
    }

    /**
     * Check if a transaction's provider order is expired
     */
    public function isProviderOrderExpired(Transaction $transaction): bool
    {
        // Check transaction's own expiry
        if ($transaction->expires_at && $transaction->expires_at->isPast()) {
            return true;
        }

        // Check provider-specific expiry from metadata
        $providerOrderCreatedAt = $transaction->metadata['provider_order_created_at'] ?? null;
        if ($providerOrderCreatedAt) {
            $expiryMinutes = $transaction->metadata['provider_order_expiry_minutes']
                ?? self::DEFAULT_ORDER_EXPIRY_MINUTES;
            $createdAt = \Carbon\Carbon::parse($providerOrderCreatedAt);

            return $createdAt->addMinutes($expiryMinutes)->isPast();
        }

        // If no explicit expiry, check if transaction is old (> 30 min) and still pending
        if ($transaction->isPending() && $transaction->created_at->diffInMinutes(now()) > self::DEFAULT_ORDER_EXPIRY_MINUTES) {
            return true;
        }

        return false;
    }

    /**
     * Get retry status for a transaction
     *
     * @return array{can_retry: bool, reason: string|null, retry_count: int, next_retry_at: string|null, expires_at: string|null}
     */
    public function getRetryStatus(Transaction $transaction): array
    {
        $validation = $this->validateRetryEligibility($transaction);
        $rateLimitCheck = $this->checkRateLimits($transaction, checkOnly: true);
        $retryCount = $this->getRetryCount($transaction);

        $nextRetryAt = null;
        if (! $rateLimitCheck['allowed'] && isset($rateLimitCheck['retry_after'])) {
            $nextRetryAt = now()->addSeconds($rateLimitCheck['retry_after'])->toIso8601String();
        }

        return [
            'can_retry' => $validation['eligible'] && $rateLimitCheck['allowed'],
            'reason' => ! $validation['eligible'] ? $validation['reason'] : ($rateLimitCheck['reason'] ?? null),
            'retry_count' => $retryCount,
            'max_retries' => self::MAX_RETRIES_PER_HOUR,
            'next_retry_at' => $nextRetryAt,
            'is_expired' => $this->isProviderOrderExpired($transaction),
            'expires_at' => $transaction->expires_at?->toIso8601String(),
        ];
    }

    /**
     * Validate if transaction is eligible for retry
     *
     * @return array{eligible: bool, reason: string|null}
     */
    private function validateRetryEligibility(Transaction $transaction): array
    {
        // Cannot retry completed transactions
        if ($transaction->isCompleted()) {
            return ['eligible' => false, 'reason' => 'Transaction is already completed'];
        }

        // Cannot retry refunded transactions
        if ($transaction->status === TransactionStatusCast::REFUNDED) {
            return ['eligible' => false, 'reason' => 'Transaction has been refunded'];
        }

        // Can only retry pending, failed, cancelled, or expired transactions
        $retryableStatuses = [
            TransactionStatusCast::PENDING,
            TransactionStatusCast::FAILED,
            TransactionStatusCast::CANCELLED,
            TransactionStatusCast::EXPIRED,
        ];

        if (! in_array($transaction->status, $retryableStatuses, true)) {
            return [
                'eligible' => false,
                'reason' => 'Transaction status does not allow retry: '.$transaction->status->getLabel(),
            ];
        }

        // Check if wallet is still active
        if ($transaction->wallet && ! $transaction->wallet->canTransact()) {
            return ['eligible' => false, 'reason' => 'Wallet is not active'];
        }

        return ['eligible' => true, 'reason' => null];
    }

    /**
     * Check rate limits for retry
     *
     * @return array{allowed: bool, reason: string|null, retry_after: int|null}
     */
    private function checkRateLimits(Transaction $transaction, bool $checkOnly = false): array
    {
        $transactionKey = "payment_retry:{$transaction->uuid}";
        $userKey = "payment_retry_user:{$transaction->wallet?->user_id}";
        $cooldownKey = "payment_retry_cooldown:{$transaction->uuid}";

        // Check cooldown
        if (Cache::has($cooldownKey)) {
            $retryAfter = Cache::get($cooldownKey) - time();

            return [
                'allowed' => false,
                'reason' => "Please wait {$retryAfter} seconds before retrying",
                'retry_after' => max(0, $retryAfter),
            ];
        }

        // Check per-transaction limit
        $transactionRetries = (int) Cache::get($transactionKey, 0);
        if ($transactionRetries >= self::MAX_RETRIES_PER_HOUR) {
            return [
                'allowed' => false,
                'reason' => 'Maximum retry attempts reached for this transaction. Please try again later.',
                'retry_after' => 3600, // 1 hour
            ];
        }

        // Check per-user limit
        $userRetries = (int) Cache::get($userKey, 0);
        if ($userRetries >= self::MAX_USER_RETRIES_PER_HOUR) {
            return [
                'allowed' => false,
                'reason' => 'Maximum payment attempts reached. Please try again later.',
                'retry_after' => 3600,
            ];
        }

        // If just checking (not actually retrying), don't set cooldown
        if (! $checkOnly) {
            // Set cooldown for next retry
            Cache::put($cooldownKey, time() + self::RETRY_COOLDOWN_SECONDS, self::RETRY_COOLDOWN_SECONDS);
        }

        return ['allowed' => true, 'reason' => null, 'retry_after' => null];
    }

    /**
     * Record a retry attempt for rate limiting
     */
    private function recordRetryAttempt(Transaction $transaction): void
    {
        $transactionKey = "payment_retry:{$transaction->uuid}";
        $userKey = "payment_retry_user:{$transaction->wallet?->user_id}";

        // Increment counters with 1 hour expiry
        Cache::increment($transactionKey);
        Cache::put($transactionKey, Cache::get($transactionKey, 1), 3600);

        if ($transaction->wallet?->user_id) {
            Cache::increment($userKey);
            Cache::put($userKey, Cache::get($userKey, 1), 3600);
        }

        // Update transaction metadata with retry count
        $metadata = $transaction->metadata ?? [];
        $metadata['retry_count'] = ($metadata['retry_count'] ?? 0) + 1;
        $metadata['last_retry_at'] = now()->toIso8601String();
        $transaction->metadata = $metadata;
        $transaction->save();
    }

    /**
     * Get current retry count for transaction
     */
    private function getRetryCount(Transaction $transaction): int
    {
        return $transaction->metadata['retry_count'] ?? 0;
    }

    /**
     * Handle expired provider order - cancel at provider if needed
     */
    private function handleExpiredProviderOrder(Transaction $transaction, PaymentProviderInterface $provider): void
    {
        if (! $this->isProviderOrderExpired($transaction)) {
            return;
        }

        $providerOrderId = $transaction->provider_order_id;

        if (! $providerOrderId) {
            return;
        }

        // Try to cancel at provider (best effort - some providers auto-expire)
        try {
            if (method_exists($provider, 'cancelOrder')) {
                $provider->cancelOrder($providerOrderId);
                Log::info('Cancelled expired provider order', [
                    'transaction_uuid' => $transaction->uuid,
                    'provider_order_id' => $providerOrderId,
                ]);
            }
        } catch (\Exception $e) {
            // Log but don't fail - order might already be expired at provider
            Log::warning('Could not cancel expired provider order', [
                'transaction_uuid' => $transaction->uuid,
                'provider_order_id' => $providerOrderId,
                'error' => $e->getMessage(),
            ]);
        }

        // Mark transaction as expired if still pending
        if ($transaction->isPending()) {
            $transaction->status = TransactionStatusCast::EXPIRED;
            $transaction->save();
        }
    }

    /**
     * Prepare transaction for retry
     */
    private function prepareTransactionForRetry(Transaction $transaction): void
    {
        // Store old provider details in metadata for audit
        $metadata = $transaction->metadata ?? [];
        $metadata['previous_attempts'] = $metadata['previous_attempts'] ?? [];
        $metadata['previous_attempts'][] = [
            'provider_order_id' => $transaction->provider_order_id,
            'provider_transaction_id' => $transaction->provider_transaction_id,
            'status' => $transaction->status->value,
            'attempted_at' => $transaction->updated_at->toIso8601String(),
        ];

        // Clear old provider details
        $transaction->provider_order_id = null;
        $transaction->provider_transaction_id = null;
        $transaction->checkout_url = null;
        $transaction->qr_code_url = null;
        $transaction->provider_response = null;
        $transaction->metadata = $metadata;

        // Reset status to pending
        $transaction->status = TransactionStatusCast::PENDING;

        // Set new expiry
        $transaction->expires_at = now()->addMinutes(self::DEFAULT_ORDER_EXPIRY_MINUTES);

        $transaction->save();
    }

    /**
     * Update transaction with new provider order details
     */
    private function updateTransactionWithNewOrder(Transaction $transaction, PaymentResponse $response): void
    {
        $metadata = $transaction->metadata ?? [];
        $metadata['provider_order_created_at'] = now()->toIso8601String();
        $metadata['provider_order_expiry_minutes'] = self::DEFAULT_ORDER_EXPIRY_MINUTES;

        $transaction->update([
            'provider_order_id' => $response->providerOrderId,
            'provider_transaction_id' => $response->providerTransactionId,
            'checkout_url' => $response->checkoutUrl,
            'qr_code_url' => $response->qrCodeUrl,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Clear rate limit for a transaction (admin use)
     */
    public function clearRateLimit(Transaction $transaction): void
    {
        $transactionKey = "payment_retry:{$transaction->uuid}";
        $cooldownKey = "payment_retry_cooldown:{$transaction->uuid}";

        Cache::forget($transactionKey);
        Cache::forget($cooldownKey);

        Log::info('Rate limit cleared for transaction', [
            'transaction_uuid' => $transaction->uuid,
        ]);
    }

    /**
     * Clear all rate limits for a user (admin use)
     */
    public function clearUserRateLimit(int $userId): void
    {
        $userKey = "payment_retry_user:{$userId}";
        Cache::forget($userKey);

        Log::info('Rate limit cleared for user', ['user_id' => $userId]);
    }
}
