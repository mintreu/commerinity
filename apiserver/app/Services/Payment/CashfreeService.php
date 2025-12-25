<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Integration;
use App\Models\Transaction;
use App\Services\MoneyService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * CashfreeService - Cashfree Payment Gateway Integration
 *
 * Handles:
 * - Creating payment orders
 * - Verifying payment status
 * - Fetching order details
 *
 * API Version: 2025-01-01
 * Docs: https://docs.cashfree.com/reference/pg-new-apis-endpoint
 */
final class CashfreeService
{
    private readonly Client $client;

    private readonly string $apiVersion;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly bool $isSandbox = true,
    ) {
        $this->apiVersion = '2025-01-01';

        $baseUrl = $this->isSandbox
            ? 'https://sandbox.cashfree.com/pg/'
            : 'https://api.cashfree.com/pg/';

        $this->client = new Client([
            'base_uri' => $baseUrl,
            'timeout' => 30,
            'http_errors' => false, // Handle errors manually
        ]);
    }

    /**
     * Create instance from Integration model
     */
    public static function fromIntegration(Integration $integration): self
    {
        $credentials = $integration->credentials;

        return new self(
            clientId: $credentials['client_id'] ?? '',
            clientSecret: $credentials['client_secret'] ?? '',
            isSandbox: $integration->is_sandbox
        );
    }

    /**
     * Get request headers for Cashfree API
     */
    private function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'x-api-version' => $this->apiVersion,
            'x-client-id' => $this->clientId,
            'x-client-secret' => $this->clientSecret,
        ];
    }

    /**
     * Create Cashfree payment order
     *
     * @return array{success: bool, cf_order_id?: int, payment_session_id?: string, error?: string}
     */
    public function createOrder(
        Transaction $transaction,
        Integration $integration,
        array $customerData,
        string $redirectSuccessUrl,
        string $redirectFailureUrl
    ): array {
        try {
            // Convert amount from paisa to rupees
            $amountInRupees = MoneyService::toRupees($transaction->amount);

            // Build Cashfree order payload
            $payload = [
                'order_id' => $transaction->uuid,
                'order_amount' => $amountInRupees,
                'order_currency' => 'INR',
                'customer_details' => [
                    'customer_id' => $customerData['mobile'] ?? $customerData['email'] ?? 'GUEST',
                    'customer_phone' => $customerData['mobile'] ?? '+919999999999',
                    'customer_name' => $customerData['name'] ?? 'Guest',
                    'customer_email' => $customerData['email'] ?? 'guest@example.com',
                ],
                'order_meta' => [
                    'return_url' => $redirectSuccessUrl,
                    'notify_url' => config('app.url').'/api/webhooks/cashfree',
                ],
                'order_note' => $transaction->purpose ?? 'Payment',
            ];

            Log::info('Creating Cashfree order', [
                'transaction_id' => $transaction->uuid,
                'amount' => $amountInRupees,
                'customer' => $customerData['name'],
            ]);

            // Call Cashfree API
            $response = $this->client->post('orders', [
                'headers' => $this->getHeaders(),
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            // Success response (200)
            if ($statusCode === 200 && isset($body['payment_session_id'])) {
                Log::info('Cashfree order created successfully', [
                    'transaction_id' => $transaction->uuid,
                    'cf_order_id' => $body['cf_order_id'] ?? null,
                ]);

                return [
                    'success' => true,
                    'cf_order_id' => $body['cf_order_id'],
                    'order_id' => $body['order_id'],
                    'payment_session_id' => $body['payment_session_id'], // ⭐ CRITICAL
                    'order_status' => $body['order_status'] ?? 'ACTIVE',
                    'order_expiry_time' => $body['order_expiry_time'] ?? null,
                    'raw_response' => $body,
                ];
            }

            // Error response
            Log::error('Cashfree order creation failed', [
                'transaction_id' => $transaction->uuid,
                'status_code' => $statusCode,
                'response' => $body,
            ]);

            return [
                'success' => false,
                'error' => $body['message'] ?? 'Failed to create Cashfree order',
                'error_code' => $body['code'] ?? null,
                'raw_response' => $body,
            ];
        } catch (GuzzleException $e) {
            Log::error('Cashfree API request failed', [
                'transaction_id' => $transaction->uuid,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Network error: '.$e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('Cashfree service exception', [
                'transaction_id' => $transaction->uuid,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch order status from Cashfree
     *
     * @return array{success: bool, order_status?: string, payment_status?: string, error?: string}
     */
    public function fetchOrderStatus(string $orderId): array
    {
        try {
            $response = $this->client->get("orders/{$orderId}", [
                'headers' => $this->getHeaders(),
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            if ($statusCode === 200) {
                return [
                    'success' => true,
                    'order_status' => $body['order_status'] ?? 'UNKNOWN',
                    'payment_status' => $body['payment_status'] ?? null,
                    'cf_order_id' => $body['cf_order_id'] ?? null,
                    'order_amount' => $body['order_amount'] ?? null,
                    'raw_response' => $body,
                ];
            }

            return [
                'success' => false,
                'error' => $body['message'] ?? 'Failed to fetch order',
                'raw_response' => $body,
            ];
        } catch (GuzzleException $e) {
            Log::error('Failed to fetch Cashfree order', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Network error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment completion
     *
     * @return array{success: bool, is_paid?: bool, error?: string}
     */
    public function verifyPayment(string $orderId): array
    {
        $orderStatus = $this->fetchOrderStatus($orderId);

        if (! $orderStatus['success']) {
            return $orderStatus;
        }

        $isPaid = in_array($orderStatus['order_status'], ['PAID', 'ACTIVE'])
            && ($orderStatus['payment_status'] === 'SUCCESS' || $orderStatus['order_status'] === 'PAID');

        return [
            'success' => true,
            'is_paid' => $isPaid,
            'order_status' => $orderStatus['order_status'],
            'payment_status' => $orderStatus['payment_status'] ?? null,
        ];
    }
}
