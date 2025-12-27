<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Casts\PaymentMethodCast;
use App\Services\Payment\Contracts\PaymentProviderInterface;
use App\Services\Payment\Contracts\PayoutProviderInterface;
use App\Services\Payment\DTOs\PaymentInitiateRequest;
use App\Services\Payment\DTOs\PaymentResponse;
use App\Services\Payment\DTOs\PaymentVerifyRequest;
use App\Services\Payment\DTOs\PayoutRequest;
use App\Services\Payment\DTOs\PayoutResponse;

interface PaymentServiceInterface
{
    /**
     * Initiate a payment
     */
    public function initiatePayment(PaymentInitiateRequest $request): PaymentResponse;

    /**
     * Verify a payment
     */
    public function verifyPayment(PaymentVerifyRequest $request): PaymentResponse;

    /**
     * Initiate a payout
     */
    public function initiatePayout(PayoutRequest $request): PayoutResponse;

    /**
     * Check payout status
     */
    public function checkPayoutStatus(string $payoutId, PaymentMethodCast $method): PayoutResponse;

    /**
     * Process refund
     */
    public function refund(string $transactionId, int $amount, PaymentMethodCast $method): PaymentResponse;

    /**
     * Get payment provider for method
     */
    public function getPaymentProvider(PaymentMethodCast $method): PaymentProviderInterface;

    /**
     * Get payout provider for method
     */
    public function getPayoutProvider(PaymentMethodCast $method): PayoutProviderInterface;

    /**
     * Register custom payment provider
     */
    public function registerPaymentProvider(string $key, PaymentProviderInterface $provider): self;

    /**
     * Register custom payout provider
     */
    public function registerPayoutProvider(string $key, PayoutProviderInterface $provider): self;

    /**
     * Get available payment methods
     *
     * @return array<string>
     */
    public function getAvailablePaymentMethods(): array;

    /**
     * Get available payout methods
     *
     * @return array<string>
     */
    public function getAvailablePayoutMethods(): array;

    /**
     * Get default payment provider key
     */
    public function getDefaultPaymentProvider(): string;

    /**
     * Get default payout provider key
     */
    public function getDefaultPayoutProvider(): string;
}
