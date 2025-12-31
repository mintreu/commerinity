<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Casts\PaymentMethodCast;
use App\Models\Integration;
use App\Services\Payment\Contracts\PaymentProviderInterface;
use App\Services\Payment\Contracts\PayoutProviderInterface;
use App\Services\Payment\DTOs\PaymentInitiateRequest;
use App\Services\Payment\DTOs\PaymentResponse;
use App\Services\Payment\DTOs\PaymentVerifyRequest;
use App\Services\Payment\DTOs\PayoutRequest;
use App\Services\Payment\DTOs\PayoutResponse;
use App\Services\Payment\Providers\CashfreePaymentProvider;
use App\Services\Payment\Providers\CashfreePayoutProvider;
use App\Services\Payment\Providers\NativePaymentProvider;
use App\Services\Payment\Providers\NativePayoutProvider;
use App\Services\Payment\Providers\RazorpayPaymentProvider;
use App\Services\Payment\Providers\RazorpayPayoutProvider;

/**
 * PaymentService - Unified payment gateway
 *
 * Routes payments/payouts to appropriate providers based on method.
 * Automatically registers providers from database integrations.
 *
 * Provider Priority (for overlapping methods):
 * 1. Cashfree (default for India)
 * 2. Razorpay (backup)
 * 3. Native (wallet, cash, COD)
 */
final class PaymentService
{
    /** @var array<string, PaymentProviderInterface> */
    private array $paymentProviders = [];

    /** @var array<string, PayoutProviderInterface> */
    private array $payoutProviders = [];

    private ?string $defaultPaymentProvider = null;

    private ?string $defaultPayoutProvider = null;

    public function __construct(
        private readonly NativePaymentProvider $nativePayment,
        private readonly NativePayoutProvider $nativePayout,
        private readonly CashfreePaymentProvider $cashfreePayment,
        private readonly CashfreePayoutProvider $cashfreePayout,
        private readonly RazorpayPaymentProvider $razorpayPayment,
        private readonly RazorpayPayoutProvider $razorpayPayout,
    ) {
        // Always register native providers
        $this->registerPaymentProvider($this->nativePayment);
        $this->registerPayoutProvider($this->nativePayout);

        // Register external providers if available
        $this->registerExternalProviders();
    }

    /**
     * Register external providers from database integrations
     */
    private function registerExternalProviders(): void
    {
        // Check and register Cashfree
        if ($this->cashfreePayment->isAvailable()) {
            $this->registerPaymentProvider($this->cashfreePayment);
        }

        if ($this->cashfreePayout->isAvailable()) {
            $this->registerPayoutProvider($this->cashfreePayout);
        }

        // Check and register Razorpay
        if ($this->razorpayPayment->isAvailable()) {
            $this->registerPaymentProvider($this->razorpayPayment);
        }

        if ($this->razorpayPayout->isAvailable()) {
            $this->registerPayoutProvider($this->razorpayPayout);
        }

        // Set defaults from database
        $this->setDefaultsFromDatabase();
    }

    /**
     * Set default providers from database
     */
    private function setDefaultsFromDatabase(): void
    {
        $defaultPayment = Integration::getDefaultPayment();
        if ($defaultPayment && isset($this->paymentProviders[$defaultPayment->slug])) {
            $this->defaultPaymentProvider = $defaultPayment->slug;
        }

        $defaultPayout = Integration::getDefaultPayout();
        if ($defaultPayout && isset($this->payoutProviders[$defaultPayout->slug])) {
            $this->defaultPayoutProvider = $defaultPayout->slug;
        }
    }

    /**
     * Register a payment provider
     */
    public function registerPaymentProvider(PaymentProviderInterface $provider): void
    {
        $this->paymentProviders[$provider->getSlug()] = $provider;
    }

    /**
     * Register a payout provider
     */
    public function registerPayoutProvider(PayoutProviderInterface $provider): void
    {
        $this->payoutProviders[$provider->getSlug()] = $provider;
    }

    /**
     * Initiate a payment
     */
    public function initiatePayment(PaymentInitiateRequest $request): PaymentResponse
    {
        $provider = $this->getPaymentProviderForMethod($request->method);

        if (! $provider) {
            return PaymentResponse::failed('No payment provider available for this method');
        }

        if (! $provider->isAvailable()) {
            return PaymentResponse::failed('Payment provider is not available');
        }

        return $provider->initiate($request);
    }

    /**
     * Initiate a payment (alias for consistency with trait usage)
     */
    public function initiate(PaymentInitiateRequest $request): PaymentResponse
    {
        return $this->initiatePayment($request);
    }

    /**
     * Verify a payment
     */
    public function verifyPayment(PaymentVerifyRequest $request, ?string $providerSlug = null): PaymentResponse
    {
        $provider = $providerSlug
            ? $this->getPaymentProvider($providerSlug)
            : $this->nativePayment;

        if (! $provider) {
            return PaymentResponse::failed('Payment provider not found');
        }

        return $provider->verify($request);
    }

    /**
     * Initiate a payout
     */
    public function initiatePayout(PayoutRequest $request): PayoutResponse
    {
        $provider = $this->getPayoutProviderForMethod($request->method);

        if (! $provider) {
            return PayoutResponse::failed('No payout provider available for this method');
        }

        if (! $provider->isAvailable()) {
            return PayoutResponse::failed('Payout provider is not available');
        }

        return $provider->initiate($request);
    }

    /**
     * Check payout status
     */
    public function checkPayoutStatus(string $payoutId, ?string $providerSlug = null): PayoutResponse
    {
        $provider = $providerSlug
            ? $this->getPayoutProvider($providerSlug)
            : $this->nativePayout;

        if (! $provider) {
            return PayoutResponse::failed('Payout provider not found');
        }

        return $provider->checkStatus($payoutId);
    }

    /**
     * Process refund
     */
    public function refund(
        string $transactionId,
        int $amountInPaisa,
        ?string $reason = null,
        ?string $providerSlug = null
    ): PaymentResponse {
        $provider = $providerSlug
            ? $this->getPaymentProvider($providerSlug)
            : $this->nativePayment;

        if (! $provider) {
            return PaymentResponse::failed('Payment provider not found');
        }

        return $provider->refund($transactionId, $amountInPaisa, $reason);
    }

    /**
     * Get payment provider by slug
     */
    public function getPaymentProvider(string $slug): ?PaymentProviderInterface
    {
        return $this->paymentProviders[$slug] ?? null;
    }

    /**
     * Get payout provider by slug
     */
    public function getPayoutProvider(string $slug): ?PayoutProviderInterface
    {
        return $this->payoutProviders[$slug] ?? null;
    }

    /**
     * Get payment provider for a specific payment method
     */
    private function getPaymentProviderForMethod(PaymentMethodCast $method): ?PaymentProviderInterface
    {
        foreach ($this->paymentProviders as $provider) {
            if (in_array($method->value, $provider->getSupportedMethods(), true)) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Get payout provider for a specific payment method
     */
    private function getPayoutProviderForMethod(PaymentMethodCast $method): ?PayoutProviderInterface
    {
        foreach ($this->payoutProviders as $provider) {
            if (in_array($method->value, $provider->getSupportedMethods(), true)) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Get all available payment methods
     *
     * @return array<string, array{slug: string, name: string, methods: array<string>}>
     */
    public function getAvailablePaymentMethods(): array
    {
        $methods = [];

        foreach ($this->paymentProviders as $provider) {
            if ($provider->isAvailable()) {
                $methods[$provider->getSlug()] = [
                    'slug' => $provider->getSlug(),
                    'name' => $provider->getName(),
                    'methods' => $provider->getSupportedMethods(),
                ];
            }
        }

        return $methods;
    }

    /**
     * Get all available payout methods
     *
     * @return array<string, array{slug: string, name: string, methods: array<string>}>
     */
    public function getAvailablePayoutMethods(): array
    {
        $methods = [];

        foreach ($this->payoutProviders as $provider) {
            if ($provider->isAvailable()) {
                $methods[$provider->getSlug()] = [
                    'slug' => $provider->getSlug(),
                    'name' => $provider->getName(),
                    'methods' => $provider->getSupportedMethods(),
                ];
            }
        }

        return $methods;
    }

    /**
     * Get default payment provider
     */
    public function getDefaultPaymentProvider(): ?PaymentProviderInterface
    {
        if ($this->defaultPaymentProvider && isset($this->paymentProviders[$this->defaultPaymentProvider])) {
            return $this->paymentProviders[$this->defaultPaymentProvider];
        }

        // Fall back to first available external provider
        foreach (['cashfree', 'razorpay'] as $slug) {
            if (isset($this->paymentProviders[$slug]) && $this->paymentProviders[$slug]->isAvailable()) {
                return $this->paymentProviders[$slug];
            }
        }

        return $this->nativePayment;
    }

    /**
     * Get default payout provider
     */
    public function getDefaultPayoutProvider(): ?PayoutProviderInterface
    {
        if ($this->defaultPayoutProvider && isset($this->payoutProviders[$this->defaultPayoutProvider])) {
            return $this->payoutProviders[$this->defaultPayoutProvider];
        }

        // Fall back to first available external provider
        foreach (['cashfree', 'razorpay'] as $slug) {
            if (isset($this->payoutProviders[$slug]) && $this->payoutProviders[$slug]->isAvailable()) {
                return $this->payoutProviders[$slug];
            }
        }

        return $this->nativePayout;
    }

    /**
     * Initiate payment with default provider (for external gateways)
     */
    public function initiateExternalPayment(PaymentInitiateRequest $request): PaymentResponse
    {
        $provider = $this->getDefaultPaymentProvider();

        if (! $provider || ! $provider->isAvailable()) {
            return PaymentResponse::failed('No payment provider available');
        }

        return $provider->initiate($request);
    }

    /**
     * Initiate payout with default provider (for external gateways)
     */
    public function initiateExternalPayout(PayoutRequest $request): PayoutResponse
    {
        $provider = $this->getDefaultPayoutProvider();

        if (! $provider || ! $provider->isAvailable()) {
            return PayoutResponse::failed('No payout provider available');
        }

        return $provider->initiate($request);
    }

    /**
     * Refresh provider registrations (useful after integration changes)
     */
    public function refreshProviders(): void
    {
        // Clear external providers
        unset($this->paymentProviders['cashfree']);
        unset($this->paymentProviders['razorpay']);
        unset($this->payoutProviders['cashfree']);
        unset($this->payoutProviders['razorpay']);

        // Clear caches
        $this->cashfreePayment->clearCache();
        $this->cashfreePayout->clearCache();
        $this->razorpayPayment->clearCache();
        $this->razorpayPayout->clearCache();

        // Re-register
        $this->registerExternalProviders();
    }

    /**
     * Check if external payment gateway is available
     */
    public function hasExternalPaymentGateway(): bool
    {
        return isset($this->paymentProviders['cashfree']) || isset($this->paymentProviders['razorpay']);
    }

    /**
     * Check if external payout gateway is available
     */
    public function hasExternalPayoutGateway(): bool
    {
        return isset($this->payoutProviders['cashfree']) || isset($this->payoutProviders['razorpay']);
    }
}
