<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Transaction;
use App\Services\Payment\DTOs\PaymentInitiateRequest;
use App\Services\Payment\DTOs\PaymentResponse;

interface PaymentRetryServiceInterface
{
    /**
     * Retry payment for a transaction
     */
    public function retryPayment(
        Transaction $transaction,
        PaymentInitiateRequest $request,
        ?string $providerSlug = null
    ): PaymentResponse;

    /**
     * Check if a transaction's provider order is expired
     */
    public function isProviderOrderExpired(Transaction $transaction): bool;

    /**
     * Get retry status for a transaction
     *
     * @return array{can_retry: bool, reason: string|null, retry_count: int, next_retry_at: string|null, expires_at: string|null}
     */
    public function getRetryStatus(Transaction $transaction): array;

    /**
     * Clear rate limit for a transaction (admin use)
     */
    public function clearRateLimit(Transaction $transaction): void;

    /**
     * Clear all rate limits for a user (admin use)
     */
    public function clearUserRateLimit(int $userId): void;
}
