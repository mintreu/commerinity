<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Payment\Providers\Cashfree;

use App\Casts\IntegrationTypeCast;
use App\Models\Integration;
use App\Services\IntegrationServices\Payment\Contracts\PaymentProviderInterface;
use App\Services\IntegrationServices\Payment\DTOs\PaymentInitiateRequest;
use App\Services\IntegrationServices\Payment\DTOs\PaymentResponse;
use App\Services\IntegrationServices\Payment\DTOs\PaymentVerifyRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CashfreePaymentProvider - Cashfree Payment Gateway Integration
 *
 * Default payment provider for India.
 * Supports: UPI, Cards, Netbanking, Wallets
 *
 * @see https://docs.cashfree.com/reference/pg-new-apis-endpoint
 */
final class CashfreePaymentProvider implements PaymentProviderInterface
{
    private const SANDBOX_URL = 'https://sandbox.cashfree.com/pg';

    private const PRODUCTION_URL = 'https://api.cashfree.com/pg';

    private const API_VERSION = '2025-01-01'; // Updated to latest API version

    private ?Integration $integration = null;

    public function getSlug(): string
    {
        return 'cashfree';
    }

    public function getName(): string
    {
        return 'Cashfree Payment Gateway';
    }

    public function isAvailable(): bool
    {
        $integration = $this->getIntegration();

        return $integration !== null && $integration->isUsable();
    }

    public function getSupportedMethods(): array
    {
        return ['cashfree', 'upi', 'card', 'netbanking', 'wallet'];
    }

    /**
     * Create a payment order with Cashfree
     */
    public function initiate(PaymentInitiateRequest $request): PaymentResponse
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return PaymentResponse::failed('Cashfree not configured');
        }

        try {
            $response = Http::withHeaders($this->getHeaders($integration))
                ->timeout(30)
                ->post($this->getBaseUrl($integration).'/orders', [
                    'order_id' => $request->transactionId,
                    'order_amount' => $request->getAmountInRupees(),
                    'order_currency' => $request->currency,
                    'customer_details' => [
                        'customer_id' => $request->userFingerprint ?? (string) $request->userId,
                        'customer_phone' => $this->formatPhone($request->customerPhone),
                        'customer_email' => $request->customerEmail ?? 'customer@mintreu.com',
                        'customer_name' => $request->customerName ?? 'Customer',
                    ],
                    'order_meta' => [
                        'return_url' => route('transaction.validate', ['transaction' => $request->transactionId]),
                        'notify_url' => route('transaction.failure', ['transaction' => $request->transactionId])
                    ],
                    'order_note' => $request->description ?? $request->purpose ?? 'Payment',
                    'order_expiry_time' => now()->addMinutes($request->expiresInMinutes ?? 30)->toIso8601String(),
                ]);


            if ($response->successful()) {
                $data = $response->json();
                // Logged
                Log::info('Cashfree order created', [
                    'order_id' => $request->transactionId,'cf_order_id' => $data['cf_order_id'] ?? null,'status' => $data['order_status'] ?? null,
                ]);

                return PaymentResponse::pending(
                    message: 'Payment order created',
                    transactionId: $request->transactionId,
                    providerOrderId: $data['cf_order_id'] ?? null,
                    checkoutUrl: route('checkout',['transaction' => $request->transactionId]),
                    metadata: [
                        'payment_session_id' => $data['payment_session_id'] ?? null,
                        'payment_link' => $data['payment_link'] ?? null,
                        'order_status' => $data['order_status'] ?? null,
                        'cf_order_id' => $data['cf_order_id'] ?? null,
                        'order_expiry_time' => $data['order_expiry_time'] ?? null,
                        'integration_id' => $this->getIntegration()->id,
                        // Store for transaction update
                        'provider_gen_id' => $data['cf_order_id'] ?? null,
                        'provider_gen_session' => $data['payment_session_id'] ?? null,
                        'provider_gen_link' => $data['payment_link'] ?? null,
                    ]
                );
            }

            $errorMessage = $response->json()['message'] ?? 'Order creation failed';
            // Logged
            Log::error('Cashfree order creation failed', [
                'response' => $response->json(), 'status' => $response->status(),
            ]);

            return PaymentResponse::failed($errorMessage, $request->transactionId);
        } catch (\Exception $e) {
            Log::error('Cashfree exception', [
                'error' => $e->getMessage(),
                'order_id' => $request->transactionId,
            ]);

            return PaymentResponse::failed('Payment gateway error: '.$e->getMessage());
        }
    }

    /**
     * Verify a payment by fetching order status
     */
    public function verify(PaymentVerifyRequest $request): PaymentResponse
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return PaymentResponse::failed('Cashfree not configured');
        }

        try {
            $response = Http::withHeaders($this->getHeaders($integration))
                ->timeout(30)
                ->get($this->getBaseUrl($integration).'/orders/'.$request->orderId);

            if ($response->successful()) {
                $data = $response->json();
                $orderStatus = $data['order_status'] ?? 'UNKNOWN';

                Log::info('Cashfree order verification', [
                    'order_id' => $request->orderId,
                    'status' => $orderStatus,
                ]);

                if ($orderStatus === 'PAID') {
                    return PaymentResponse::success(
                        message: 'Payment verified successfully',
                        transactionId: $request->orderId,
                        providerOrderId: $data['cf_order_id'] ?? null,
                        providerTransactionId: $data['cf_payment_id'] ?? null,
                        metadata: $data
                    );
                }

                if (in_array($orderStatus, ['ACTIVE', 'PENDING'])) {
                    return PaymentResponse::pending(
                        message: 'Payment is pending',
                        transactionId: $request->orderId,
                        providerOrderId: $data['cf_order_id'] ?? null,
                        metadata: $data
                    );
                }

                return PaymentResponse::failed(
                    'Payment not completed: '.$orderStatus,
                    $request->orderId,
                    $data
                );
            }

            return PaymentResponse::failed('Verification request failed', $request->orderId);
        } catch (\Exception $e) {
            Log::error('Cashfree verify exception', [
                'error' => $e->getMessage(),
                'order_id' => $request->orderId,
            ]);

            return PaymentResponse::failed('Verification error: '.$e->getMessage());
        }
    }

    /**
     * Process a refund
     */
    public function refund(string $transactionId, int $amountInPaisa, ?string $reason = null): PaymentResponse
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return PaymentResponse::failed('Cashfree not configured');
        }

        try {
            $refundId = 'REF-'.$transactionId.'-'.time();

            $response = Http::withHeaders($this->getHeaders($integration))
                ->timeout(30)
                ->post($this->getBaseUrl($integration).'/orders/'.$transactionId.'/refunds', [
                    'refund_amount' => $amountInPaisa / 100,
                    'refund_id' => $refundId,
                    'refund_note' => $reason ?? 'Refund requested',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Cashfree refund initiated', [
                    'order_id' => $transactionId,
                    'refund_id' => $refundId,
                    'cf_refund_id' => $data['cf_refund_id'] ?? null,
                ]);

                return PaymentResponse::success(
                    status: PaymentResponse::STATUS_PROCESSING,
                    message: 'Refund initiated',
                    transactionId: $transactionId,
                    providerTransactionId: $data['cf_refund_id'] ?? null,
                    metadata: $data
                );
            }

            $errorMessage = $response->json()['message'] ?? 'Refund failed';
            Log::error('Cashfree refund failed', [
                'response' => $response->json(),
                'order_id' => $transactionId,
            ]);

            return PaymentResponse::failed($errorMessage, $transactionId);
        } catch (\Exception $e) {
            Log::error('Cashfree refund exception', [
                'error' => $e->getMessage(),
                'order_id' => $transactionId,
            ]);

            return PaymentResponse::failed('Refund error: '.$e->getMessage());
        }
    }

    /**
     * Get order payments (for reconciliation)
     */
    public function getOrderPayments(string $orderId): ?array
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return null;
        }

        try {
            $response = Http::withHeaders($this->getHeaders($integration))
                ->timeout(30)
                ->get($this->getBaseUrl($integration).'/orders/'.$orderId.'/payments');

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Cashfree get payments exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $timestamp, string $rawBody, string $signature): bool
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return false;
        }

        $webhookSecret = $integration->getWebhookSecret();
        if (! $webhookSecret) {
            Log::warning('Cashfree webhook secret not configured');

            return false;
        }

        $computedSignature = base64_encode(
            hash_hmac('sha256', $timestamp.$rawBody, $webhookSecret, true)
        );

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Get the integration configuration
     */
    private function getIntegration(): ?Integration
    {
        if ($this->integration === null) {
            $this->integration = Integration::query()
            ->bySlug('cashfree')
            ->ofType(IntegrationTypeCast::PAYMENT->value)
                ->active()
                ->first();
        }

        return $this->integration;
    }

    /**
     * Get API headers
     */
    private function getHeaders(Integration $integration): array
    {
        return [
            'x-client-id' => $integration->getCredential('key'),
            'x-client-secret' => $integration->getCredential('secret'),
            'x-api-version' => self::API_VERSION,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Get base URL based on environment
     */
    private function getBaseUrl(Integration $integration): string
    {
        return $this->isSandboxMode($integration) ? self::SANDBOX_URL : self::PRODUCTION_URL;
    }

    /**
     * Resolve sandbox mode with env override support.
     * Priority:
     * 1) services.payment.cashfree.sandbox (CASH_FREE_PAYMENT_SANDBOX) when set
     * 2) integration.is_sandbox
     */
    public function isSandboxMode(?Integration $integration = null): bool
    {
        $sandboxOverride = config('services.payment.cashfree.sandbox');

        if ($sandboxOverride !== null && $sandboxOverride !== '') {
            return filter_var($sandboxOverride, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
                ?? (bool) $sandboxOverride;
        }

        $resolvedIntegration = $integration ?? $this->getIntegration();

        return (bool) ($resolvedIntegration?->is_sandbox ?? true);
    }

    /**
     * Format phone number for Cashfree (10 digits without country code)
     */
    private function formatPhone(?string $phone): string
    {
        if (! $phone) {
            return '9999999999';
        }

        // Remove +91 or 91 prefix
        $phone = preg_replace('/^\+?91/', '', $phone);

        // Remove any non-digits
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Ensure it's 10 digits
        if (strlen($phone) === 10) {
            return $phone;
        }

        // If longer, take last 10 digits
        if (strlen($phone) > 10) {
            return substr($phone, -10);
        }

        return '9999999999';
    }

    /**
     * Cancel an order at Cashfree
     *
     * Note: Cashfree doesn't have a direct cancel API for orders.
     * Orders auto-expire based on order_expiry_time.
     * This method returns true as orders will naturally expire.
     */
    public function cancelOrder(string $orderId): bool
    {
        // Cashfree orders auto-expire, but we can check if it's still active
        $integration = $this->getIntegration();
        if (! $integration) {
            return false;
        }

        try {
            $response = Http::withHeaders($this->getHeaders($integration))
                ->timeout(30)
                ->get($this->getBaseUrl($integration).'/orders/'.$orderId);

            if ($response->successful()) {
                $data = $response->json();
                $orderStatus = $data['order_status'] ?? 'UNKNOWN';

                // If order is already expired, cancelled, or paid - no action needed
                if (in_array($orderStatus, ['EXPIRED', 'TERMINATED', 'PAID'])) {
                    Log::info('Cashfree order already in final state', [
                        'order_id' => $orderId,
                        'status' => $orderStatus,
                    ]);

                    return true;
                }

                // For active orders, they will auto-expire
                // Cashfree doesn't provide a cancel API
                Log::info('Cashfree order will auto-expire', [
                    'order_id' => $orderId,
                    'status' => $orderStatus,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Cashfree cancel order check failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Clear cached integration (useful for testing)
     */
    public function clearCache(): void
    {
        $this->integration = null;
    }
}
