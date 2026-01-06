<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Payment;

use App\Casts\PaymentMethodCast;
use App\Models\Integration;
use App\Services\IntegrationServices\Payment\Contracts\PaymentProviderInterface;
use App\Services\IntegrationServices\Payment\DTOs\PaymentInitiateRequest;
use App\Services\IntegrationServices\Payment\DTOs\PaymentResponse;
use App\Services\IntegrationServices\Payment\DTOs\PaymentVerifyRequest;
use App\Services\IntegrationServices\Payment\Providers\Cashfree\CashfreePaymentProvider;
use App\Services\IntegrationServices\Payment\Providers\NativePaymentProvider;
use App\Services\IntegrationServices\Payment\Providers\RazorpayPaymentProvider;

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
    /** @var array<string, \App\Services\IntegrationServices\Payment\Contracts\PaymentProviderInterface> */
    private array $paymentProviders = [];

    private ?string $defaultPaymentProvider = null;

    public function __construct(
        private readonly NativePaymentProvider $nativePayment,
        private readonly CashfreePaymentProvider $cashfreePayment,
        private readonly RazorpayPaymentProvider $razorpayPayment,
    ) {
        // Always register native providers
        $this->registerPaymentProvider($this->nativePayment);
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
        // Check and register Razorpay
        if ($this->razorpayPayment->isAvailable()) {
            $this->registerPaymentProvider($this->razorpayPayment);
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
    }

    /**
     * Register a payment provider
     */
    public function registerPaymentProvider(PaymentProviderInterface $provider): void
    {
        $this->paymentProviders[$provider->getSlug()] = $provider;
    }




    public function provider(string $provider):PaymentProviderInterface
    {
        try {
            if (isset($this->paymentProviders[$provider]))
            {
                return $this->paymentProviders[$provider];
            }
            return $this->paymentProviders[$this->defaultPaymentProvider];
        }catch (\Throwable $t)
        {
            report($t);
        }
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
     * Refresh provider registrations (useful after integration changes)
     */
    public function refreshProviders(): void
    {
        // Clear external providers
        unset($this->paymentProviders['cashfree']);
        unset($this->paymentProviders['razorpay']);

        // Clear caches
        $this->cashfreePayment->clearCache();
        $this->razorpayPayment->clearCache();

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

}
