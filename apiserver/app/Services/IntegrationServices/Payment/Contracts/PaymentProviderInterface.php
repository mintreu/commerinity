<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Payment\Contracts;

use App\Services\IntegrationServices\Payment\DTOs\PaymentInitiateRequest;
use App\Services\IntegrationServices\Payment\DTOs\PaymentResponse;
use App\Services\IntegrationServices\Payment\DTOs\PaymentVerifyRequest;

/**
 * PaymentProviderInterface - Contract for all payment providers
 *
 * Implementations:
 * - NativePaymentProvider (wallet, cash, COD, bank transfer)
 * - RazorpayPaymentProvider (future)
 * - CashfreePaymentProvider (future)
 */
interface PaymentProviderInterface
{
    /**
     * Get the provider slug/identifier
     */
    public function getSlug(): string;

    /**
     * Get human-readable provider name
     */
    public function getName(): string;

    /**
     * Check if the provider is properly configured and available
     */
    public function isAvailable(): bool;

    /**
     * Initiate a payment request
     *
     * @param  PaymentInitiateRequest  $request  Payment details
     * @return \App\Services\IntegrationServices\Payment\DTOs\PaymentResponse Response with order/session details
     */
    public function initiate(PaymentInitiateRequest $request): PaymentResponse;

    /**
     * Verify a payment after callback/webhook
     *
     * @param  \App\Services\IntegrationServices\Payment\DTOs\PaymentVerifyRequest  $request  Verification details from provider
     * @return PaymentResponse Response with verification status
     */
    public function verify(PaymentVerifyRequest $request): PaymentResponse;

    /**
     * Process a refund
     *
     * @param  string  $transactionId  Original transaction ID
     * @param  int  $amountInPaisa  Amount to refund
     * @param  string|null  $reason  Refund reason
     * @return PaymentResponse Response with refund status
     */
    public function refund(string $transactionId, int $amountInPaisa, ?string $reason = null): PaymentResponse;

    /**
     * Get supported payment methods for this provider
     *
     * @return array<string> List of supported payment method slugs
     */
    public function getSupportedMethods(): array;

    /**
     * Cancel an order at the provider (optional - for retry support)
     *
     * @param  string  $orderId  Provider's order ID to cancel
     * @return bool Whether cancellation was successful
     */
    public function cancelOrder(string $orderId): bool;
}
