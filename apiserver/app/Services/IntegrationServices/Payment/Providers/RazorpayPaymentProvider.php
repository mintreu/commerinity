<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Payment\Providers;

use App\Casts\IntegrationTypeCast;
use App\Models\Integration;
use App\Services\IntegrationServices\Payment\Contracts\PaymentProviderInterface;
use App\Services\IntegrationServices\Payment\DTOs\PaymentInitiateRequest;
use App\Services\IntegrationServices\Payment\DTOs\PaymentResponse;
use App\Services\IntegrationServices\Payment\DTOs\PaymentVerifyRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * RazorpayPaymentProvider - Razorpay Payment Gateway Integration
 *
 * Backup payment provider for India.
 * Supports: UPI, Cards, Netbanking, Wallets, EMI
 *
 * @see https://razorpay.com/docs/api/
 */
final class RazorpayPaymentProvider implements PaymentProviderInterface
{
    private const API_URL = 'https://api.razorpay.com/v1';

    private ?Integration $integration = null;

    public function getSlug(): string
    {
        return 'razorpay';
    }

    public function getName(): string
    {
        return 'Razorpay';
    }

    public function isAvailable(): bool
    {
        $integration = $this->getIntegration();

        return $integration !== null && $integration->isUsable();
    }

    public function getSupportedMethods(): array
    {
        return ['razorpay', 'upi', 'card', 'netbanking', 'wallet', 'emi'];
    }

    /**
     * Create a payment order with Razorpay
     */
    public function initiate(PaymentInitiateRequest $request): PaymentResponse
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return PaymentResponse::failed('Razorpay not configured');
        }

        try {
            $response = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->post(self::API_URL.'/orders', [
                    'amount' => $request->amountInPaisa, // Razorpay expects paisa
                    'currency' => $request->currency,
                    'receipt' => $request->transactionId,
                    'notes' => [
                        'user_id' => (string) $request->userId,
                        'wallet_id' => (string) $request->walletId,
                        'purpose' => $request->purpose ?? 'payment',
                        'description' => $request->description ?? '',
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Razorpay order created', [
                    'order_id' => $data['id'] ?? null,
                    'receipt' => $request->transactionId,
                    'status' => $data['status'] ?? null,
                ]);

                return PaymentResponse::pending(
                    message: 'Payment order created',
                    transactionId: $request->transactionId,
                    providerOrderId: $data['id'] ?? null,
                    metadata: [
                        'razorpay_order_id' => $data['id'] ?? null,
                        'razorpay_key_id' => $integration->getCredential('key_id'),
                        'amount' => $data['amount'] ?? $request->amountInPaisa,
                        'currency' => $data['currency'] ?? $request->currency,
                        'status' => $data['status'] ?? null,
                    ]
                );
            }

            $errorMessage = $response->json()['error']['description'] ?? 'Order creation failed';
            Log::error('Razorpay order creation failed', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return PaymentResponse::failed($errorMessage, $request->transactionId);
        } catch (\Exception $e) {
            Log::error('Razorpay exception', [
                'error' => $e->getMessage(),
                'order_id' => $request->transactionId,
            ]);

            return PaymentResponse::failed('Payment gateway error: '.$e->getMessage());
        }
    }

    /**
     * Verify a payment signature from Razorpay
     */
    public function verify(PaymentVerifyRequest $request): PaymentResponse
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return PaymentResponse::failed('Razorpay not configured');
        }

        // Razorpay verification requires signature
        if (! $request->providerSignature) {
            return PaymentResponse::failed('Signature required for verification');
        }

        try {
            // Verify signature
            $expectedSignature = hash_hmac(
                'sha256',
                $request->providerOrderId.'|'.$request->providerTransactionId,
                $integration->getCredential('key_secret')
            );

            if (! hash_equals($expectedSignature, $request->providerSignature)) {
                Log::warning('Razorpay signature mismatch', [
                    'order_id' => $request->orderId,
                ]);

                return PaymentResponse::failed('Signature verification failed', $request->orderId);
            }

            // Fetch payment details to confirm
            $response = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->get(self::API_URL.'/payments/'.$request->providerTransactionId);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['status'] ?? 'unknown';

                Log::info('Razorpay payment verified', [
                    'order_id' => $request->orderId,
                    'payment_id' => $request->providerTransactionId,
                    'status' => $status,
                ]);

                if ($status === 'captured') {
                    return PaymentResponse::success(
                        message: 'Payment verified successfully',
                        transactionId: $request->orderId,
                        providerOrderId: $request->providerOrderId,
                        providerTransactionId: $request->providerTransactionId,
                        metadata: $data
                    );
                }

                if ($status === 'authorized') {
                    // Need to capture the payment
                    return PaymentResponse::pending(
                        message: 'Payment authorized, capture pending',
                        transactionId: $request->orderId,
                        providerOrderId: $request->providerOrderId,
                        metadata: $data
                    );
                }

                return PaymentResponse::failed('Payment not completed: '.$status, $request->orderId, $data);
            }

            return PaymentResponse::failed('Payment verification failed', $request->orderId);
        } catch (\Exception $e) {
            Log::error('Razorpay verify exception', [
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
            return PaymentResponse::failed('Razorpay not configured');
        }

        try {
            $response = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->post(self::API_URL.'/payments/'.$transactionId.'/refund', [
                    'amount' => $amountInPaisa,
                    'notes' => [
                        'reason' => $reason ?? 'Refund requested',
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Razorpay refund initiated', [
                    'payment_id' => $transactionId,
                    'refund_id' => $data['id'] ?? null,
                ]);

                return PaymentResponse::success(
                    status: PaymentResponse::STATUS_PROCESSING,
                    message: 'Refund initiated',
                    transactionId: $transactionId,
                    providerTransactionId: $data['id'] ?? null,
                    metadata: $data
                );
            }

            $errorMessage = $response->json()['error']['description'] ?? 'Refund failed';
            Log::error('Razorpay refund failed', [
                'response' => $response->json(),
                'payment_id' => $transactionId,
            ]);

            return PaymentResponse::failed($errorMessage, $transactionId);
        } catch (\Exception $e) {
            Log::error('Razorpay refund exception', [
                'error' => $e->getMessage(),
                'payment_id' => $transactionId,
            ]);

            return PaymentResponse::failed('Refund error: '.$e->getMessage());
        }
    }

    /**
     * Capture an authorized payment
     */
    public function capture(string $paymentId, int $amountInPaisa): PaymentResponse
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return PaymentResponse::failed('Razorpay not configured');
        }

        try {
            $response = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->post(self::API_URL.'/payments/'.$paymentId.'/capture', [
                    'amount' => $amountInPaisa,
                    'currency' => 'INR',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return PaymentResponse::success(
                    message: 'Payment captured',
                    providerTransactionId: $paymentId,
                    metadata: $data
                );
            }

            return PaymentResponse::failed('Capture failed');
        } catch (\Exception $e) {
            Log::error('Razorpay capture exception', ['error' => $e->getMessage()]);

            return PaymentResponse::failed('Capture error: '.$e->getMessage());
        }
    }

    /**
     * Fetch payment details
     */
    public function fetchPayment(string $paymentId): ?array
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return null;
        }

        try {
            $response = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->get(self::API_URL.'/payments/'.$paymentId);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Razorpay fetch payment exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Fetch order details
     */
    public function fetchOrder(string $orderId): ?array
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return null;
        }

        try {
            $response = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->get(self::API_URL.'/orders/'.$orderId);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Razorpay fetch order exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $rawBody, string $signature): bool
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return false;
        }

        $webhookSecret = $integration->getWebhookSecret();
        if (! $webhookSecret) {
            Log::warning('Razorpay webhook secret not configured');

            return false;
        }

        $expectedSignature = hash_hmac('sha256', $rawBody, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get the integration configuration
     */
    private function getIntegration(): ?Integration
    {
        if ($this->integration === null) {
            $this->integration = Integration::query()
                ->bySlug('razorpay')
                ->ofType(IntegrationTypeCast::PAYMENT->value)
                ->active()
                ->first();
        }

        return $this->integration;
    }

    /**
     * Cancel an order at Razorpay
     *
     * Note: Razorpay doesn't allow cancelling orders directly.
     * Orders remain valid until they expire (default: no expiry).
     * This method checks order status and returns appropriately.
     */
    public function cancelOrder(string $orderId): bool
    {
        $integration = $this->getIntegration();
        if (! $integration) {
            return false;
        }

        try {
            $response = Http::withBasicAuth(
                $integration->getCredential('key_id'),
                $integration->getCredential('key_secret')
            )
                ->timeout(30)
                ->get(self::API_URL.'/orders/'.$orderId);

            if ($response->successful()) {
                $data = $response->json();
                $orderStatus = $data['status'] ?? 'unknown';

                // Check if order is already in a final state
                if (in_array($orderStatus, ['paid', 'attempted'])) {
                    Log::info('Razorpay order in final state', [
                        'order_id' => $orderId,
                        'status' => $orderStatus,
                    ]);

                    return $orderStatus === 'paid';
                }

                // For 'created' orders, they can still be used
                // Razorpay doesn't have cancel API, orders just become stale
                Log::info('Razorpay order status checked for cancel', [
                    'order_id' => $orderId,
                    'status' => $orderStatus,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Razorpay cancel order check failed', [
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
