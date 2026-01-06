# Payment Providers Implementation Guide

> **Purpose**: Step-by-step implementation guide for Cashfree, Razorpay, and Stripe providers.
> All patterns extracted from JetPax/Popkult reference projects - NO NEED TO SCAN AGAIN.

---

## 1. IMPLEMENTATION PRIORITY

```
PHASE 1 (IMMEDIATE):
├── CashfreePaymentProvider (Default for India)
├── CashfreePayoutProvider (Default for withdrawals)
└── Webhook controllers with signature verification

PHASE 2 (WEEK 2):
├── RazorpayPaymentProvider (Backup)
├── RazorpayPayoutProvider (Backup via RazorpayX)
└── Provider failover logic

PHASE 3 (FUTURE):
├── StripePaymentProvider (International)
└── Currency conversion handling
```

---

## 2. CASHFREE IMPLEMENTATION

### 2.1 Provider Class Structure

```php
<?php

declare(strict_types=1);

namespace App\Services\Payment\Providers;

use App\Models\Integration;use App\Services\IntegrationServices\Payment\Contracts\PaymentProviderInterface;use App\Services\IntegrationServices\Payment\DTOs\PaymentInitiateRequest;use App\Services\IntegrationServices\Payment\DTOs\PaymentResponse;use App\Services\IntegrationServices\Payment\DTOs\PaymentVerifyRequest;use Illuminate\Support\Facades\Http;use Illuminate\Support\Facades\Log;

final class CashfreePaymentProvider implements PaymentProviderInterface
{
    private const SANDBOX_URL = 'https://sandbox.cashfree.com/pg';
    private const PRODUCTION_URL = 'https://api.cashfree.com/pg';
    private const API_VERSION = '2023-08-01';

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
        return $this->getIntegration()?->is_active ?? false;
    }

    public function getSupportedMethods(): array
    {
        return ['upi', 'card', 'netbanking', 'wallet'];
    }

    public function initiate(PaymentInitiateRequest $request): PaymentResponse
    {
        $integration = $this->getIntegration();
        if (!$integration) {
            return PaymentResponse::failed('Cashfree not configured');
        }

        $credentials = $integration->decrypted_credentials;

        try {
            $response = Http::withHeaders([
                'x-client-id' => $credentials['app_id'],
                'x-client-secret' => $credentials['secret_key'],
                'x-api-version' => self::API_VERSION,
                'Content-Type' => 'application/json',
            ])->post($this->getBaseUrl($credentials) . '/orders', [
                'order_id' => $request->transactionId,
                'order_amount' => $request->amount / 100, // Convert paisa to rupees
                'order_currency' => 'INR',
                'customer_details' => [
                    'customer_id' => (string) $request->userId,
                    'customer_phone' => $request->phone,
                    'customer_email' => $request->email,
                    'customer_name' => $request->name,
                ],
                'order_meta' => [
                    'return_url' => $request->callbackUrl . '?order_id={order_id}',
                    'notify_url' => config('app.url') . '/api/webhooks/cashfree',
                ],
                'order_note' => $request->description,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return PaymentResponse::success(
                    transactionId: $request->transactionId,
                    providerReference: $data['cf_order_id'],
                    paymentUrl: null, // Use payment_session_id with JS SDK
                    metadata: [
                        'payment_session_id' => $data['payment_session_id'],
                        'order_status' => $data['order_status'],
                    ]
                );
            }

            Log::error('Cashfree order creation failed', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return PaymentResponse::failed(
                $response->json()['message'] ?? 'Order creation failed'
            );
        } catch (\Exception $e) {
            Log::error('Cashfree exception', ['error' => $e->getMessage()]);
            return PaymentResponse::failed('Payment gateway error');
        }
    }

    public function verify(PaymentVerifyRequest $request): PaymentResponse
    {
        $integration = $this->getIntegration();
        if (!$integration) {
            return PaymentResponse::failed('Cashfree not configured');
        }

        $credentials = $integration->decrypted_credentials;

        try {
            $response = Http::withHeaders([
                'x-client-id' => $credentials['app_id'],
                'x-client-secret' => $credentials['secret_key'],
                'x-api-version' => self::API_VERSION,
            ])->get($this->getBaseUrl($credentials) . '/orders/' . $request->orderId);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['order_status'] === 'PAID') {
                    return PaymentResponse::success(
                        transactionId: $request->orderId,
                        providerReference: $data['cf_order_id'],
                        metadata: $data
                    );
                }

                return PaymentResponse::failed('Payment not completed: ' . $data['order_status']);
            }

            return PaymentResponse::failed('Verification failed');
        } catch (\Exception $e) {
            Log::error('Cashfree verify exception', ['error' => $e->getMessage()]);
            return PaymentResponse::failed('Verification error');
        }
    }

    public function refund(string $transactionId, int $amountInPaisa, ?string $reason = null): PaymentResponse
    {
        $integration = $this->getIntegration();
        if (!$integration) {
            return PaymentResponse::failed('Cashfree not configured');
        }

        $credentials = $integration->decrypted_credentials;

        try {
            $response = Http::withHeaders([
                'x-client-id' => $credentials['app_id'],
                'x-client-secret' => $credentials['secret_key'],
                'x-api-version' => self::API_VERSION,
            ])->post($this->getBaseUrl($credentials) . '/orders/' . $transactionId . '/refunds', [
                'refund_amount' => $amountInPaisa / 100,
                'refund_id' => 'REF-' . $transactionId . '-' . time(),
                'refund_note' => $reason ?? 'Refund requested',
            ]);

            if ($response->successful()) {
                return PaymentResponse::success(
                    transactionId: $transactionId,
                    providerReference: $response->json()['cf_refund_id'] ?? null,
                    metadata: $response->json()
                );
            }

            return PaymentResponse::failed($response->json()['message'] ?? 'Refund failed');
        } catch (\Exception $e) {
            Log::error('Cashfree refund exception', ['error' => $e->getMessage()]);
            return PaymentResponse::failed('Refund error');
        }
    }

    private function getIntegration(): ?Integration
    {
        if ($this->integration === null) {
            $this->integration = Integration::where('provider', 'cashfree')
                ->where('type', 'payment')
                ->where('is_active', true)
                ->first();
        }

        return $this->integration;
    }

    private function getBaseUrl(array $credentials): string
    {
        return ($credentials['environment'] ?? 'sandbox') === 'production'
            ? self::PRODUCTION_URL
            : self::SANDBOX_URL;
    }
}
```

### 2.2 Cashfree Payout Provider

```php
<?php

declare(strict_types=1);

namespace App\Services\Payment\Providers;

use App\Models\BeneficiaryAccount;use App\Models\Integration;use App\Services\IntegrationServices\Payout\Contracts\PayoutProviderInterface;use App\Services\IntegrationServices\Payout\DTOs\PayoutRequest;use App\Services\IntegrationServices\Payout\DTOs\PayoutResponse;use Illuminate\Support\Facades\Http;use Illuminate\Support\Facades\Log;

final class CashfreePayoutProvider implements PayoutProviderInterface
{
    private const SANDBOX_URL = 'https://payout-gamma.cashfree.com/payout/v1';
    private const PRODUCTION_URL = 'https://payout-api.cashfree.com/payout/v1';

    private ?Integration $integration = null;
    private ?string $bearerToken = null;

    public function getSlug(): string
    {
        return 'cashfree';
    }

    public function getName(): string
    {
        return 'Cashfree Payouts';
    }

    public function isAvailable(): bool
    {
        return $this->getIntegration()?->is_active ?? false;
    }

    public function getSupportedMethods(): array
    {
        return ['bank_transfer', 'upi'];
    }

    public function initiate(PayoutRequest $request): PayoutResponse
    {
        $integration = $this->getIntegration();
        if (!$integration) {
            return PayoutResponse::failed('Cashfree Payouts not configured');
        }

        // Step 1: Ensure beneficiary exists
        $beneficiary = BeneficiaryAccount::find($request->beneficiaryId);
        if (!$beneficiary) {
            return PayoutResponse::failed('Beneficiary not found');
        }

        // Step 2: Add beneficiary to Cashfree if not already
        if (!$beneficiary->provider_beneficiary_id) {
            $addResult = $this->addBeneficiary($beneficiary);
            if (!$addResult['success']) {
                return PayoutResponse::failed($addResult['message']);
            }
            $beneficiary->update(['provider_beneficiary_id' => $addResult['bene_id']]);
        }

        // Step 3: Request transfer
        try {
            $response = Http::withHeaders($this->getAuthHeaders())
                ->post($this->getBaseUrl() . '/requestTransfer', [
                    'beneId' => $beneficiary->provider_beneficiary_id,
                    'amount' => $request->amount / 100, // paisa to rupees
                    'transferId' => $request->transactionId,
                    'transferMode' => $beneficiary->type === 'upi' ? 'upi' : 'banktransfer',
                    'remarks' => $request->description ?? 'Payout',
                ]);

            if ($response->successful() && $response->json()['status'] === 'SUCCESS') {
                return PayoutResponse::success(
                    transactionId: $request->transactionId,
                    providerReference: $response->json()['data']['referenceId'] ?? null,
                    status: 'processing',
                    metadata: $response->json()
                );
            }

            return PayoutResponse::failed(
                $response->json()['message'] ?? 'Transfer request failed'
            );
        } catch (\Exception $e) {
            Log::error('Cashfree payout exception', ['error' => $e->getMessage()]);
            return PayoutResponse::failed('Payout error');
        }
    }

    public function checkStatus(string $payoutId): PayoutResponse
    {
        try {
            $response = Http::withHeaders($this->getAuthHeaders())
                ->get($this->getBaseUrl() . '/getTransferStatus', [
                    'transferId' => $payoutId,
                ]);

            if ($response->successful()) {
                $data = $response->json()['data']['transfer'] ?? [];
                $status = match ($data['status'] ?? 'UNKNOWN') {
                    'SUCCESS' => 'completed',
                    'PENDING', 'RECEIVED' => 'processing',
                    'FAILED', 'REVERSED' => 'failed',
                    default => 'unknown',
                };

                return PayoutResponse::success(
                    transactionId: $payoutId,
                    providerReference: $data['referenceId'] ?? null,
                    status: $status,
                    metadata: $data
                );
            }

            return PayoutResponse::failed('Status check failed');
        } catch (\Exception $e) {
            Log::error('Cashfree status check exception', ['error' => $e->getMessage()]);
            return PayoutResponse::failed('Status check error');
        }
    }

    private function addBeneficiary(BeneficiaryAccount $beneficiary): array
    {
        try {
            $payload = [
                'beneId' => 'BENE-' . $beneficiary->id,
                'name' => $beneficiary->account_holder_name,
                'email' => $beneficiary->user->email,
                'phone' => $beneficiary->user->phone,
                'address1' => 'India',
            ];

            if ($beneficiary->type === 'bank') {
                $payload['bankAccount'] = $beneficiary->account_number;
                $payload['ifsc'] = $beneficiary->ifsc_code;
            } else {
                $payload['vpa'] = $beneficiary->upi_id;
            }

            $response = Http::withHeaders($this->getAuthHeaders())
                ->post($this->getBaseUrl() . '/addBeneficiary', $payload);

            if ($response->successful() || $response->json()['subCode'] === '409') {
                // 409 means already exists - that's fine
                return [
                    'success' => true,
                    'bene_id' => 'BENE-' . $beneficiary->id,
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to add beneficiary',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function getAuthHeaders(): array
    {
        if (!$this->bearerToken) {
            $this->authenticate();
        }

        return [
            'Authorization' => 'Bearer ' . $this->bearerToken,
            'Content-Type' => 'application/json',
        ];
    }

    private function authenticate(): void
    {
        $credentials = $this->getIntegration()->decrypted_credentials;

        $response = Http::post($this->getBaseUrl() . '/authorize', [
            'clientId' => $credentials['app_id'],
            'clientSecret' => $credentials['secret_key'],
        ]);

        if ($response->successful()) {
            $this->bearerToken = $response->json()['data']['token'];
        }
    }

    private function getIntegration(): ?Integration
    {
        if ($this->integration === null) {
            $this->integration = Integration::where('provider', 'cashfree')
                ->where('type', 'payout')
                ->where('is_active', true)
                ->first();
        }

        return $this->integration;
    }

    private function getBaseUrl(): string
    {
        $credentials = $this->getIntegration()->decrypted_credentials ?? [];
        return ($credentials['environment'] ?? 'sandbox') === 'production'
            ? self::PRODUCTION_URL
            : self::SANDBOX_URL;
    }
}
```

### 2.3 Webhook Controller

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Webhooks;

use App\Events\TransactionConfirmed;
use App\Models\Integration;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class CashfreeWebhookController
{
    public function handle(Request $request): JsonResponse
    {
        // 1. Verify webhook signature
        if (!$this->verifySignature($request)) {
            Log::warning('Cashfree webhook: Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // 2. Parse event type
        $eventType = $request->input('type');

        Log::info('Cashfree webhook received', [
            'type' => $eventType,
            'order_id' => $request->input('data.order.order_id'),
        ]);

        // 3. Handle event
        match ($eventType) {
            'PAYMENT_SUCCESS_WEBHOOK' => $this->handlePaymentSuccess($request),
            'PAYMENT_FAILED_WEBHOOK' => $this->handlePaymentFailed($request),
            'REFUND_STATUS_WEBHOOK' => $this->handleRefund($request),
            default => Log::info('Unhandled Cashfree webhook', ['type' => $eventType]),
        };

        return response()->json(['status' => 'ok']);
    }

    private function verifySignature(Request $request): bool
    {
        $integration = Integration::where('provider', 'cashfree')
            ->where('type', 'payment')
            ->where('is_active', true)
            ->first();

        if (!$integration) {
            return false;
        }

        $webhookSecret = $integration->decrypted_credentials['webhook_secret'] ?? null;
        if (!$webhookSecret) {
            return false;
        }

        $timestamp = $request->header('x-webhook-timestamp');
        $signature = $request->header('x-webhook-signature');
        $rawBody = $request->getContent();

        $computedSignature = base64_encode(
            hash_hmac('sha256', $timestamp . $rawBody, $webhookSecret, true)
        );

        return hash_equals($computedSignature, $signature);
    }

    private function handlePaymentSuccess(Request $request): void
    {
        $orderId = $request->input('data.order.order_id');
        $transaction = Transaction::where('uuid', $orderId)->first();

        if (!$transaction) {
            Log::error('Cashfree webhook: Transaction not found', ['order_id' => $orderId]);
            return;
        }

        if ($transaction->status === 'completed') {
            return; // Already processed
        }

        $transaction->update([
            'status' => 'completed',
            'provider_reference' => $request->input('data.payment.cf_payment_id'),
            'provider_response' => $request->all(),
            'completed_at' => now(),
        ]);

        event(new TransactionConfirmed($transaction));
    }

    private function handlePaymentFailed(Request $request): void
    {
        $orderId = $request->input('data.order.order_id');
        $transaction = Transaction::where('uuid', $orderId)->first();

        if (!$transaction) {
            return;
        }

        $transaction->update([
            'status' => 'failed',
            'provider_response' => $request->all(),
        ]);
    }

    private function handleRefund(Request $request): void
    {
        $orderId = $request->input('data.refund.order_id');
        $transaction = Transaction::where('uuid', $orderId)->first();

        if (!$transaction) {
            return;
        }

        $refundStatus = $request->input('data.refund.refund_status');

        if ($refundStatus === 'SUCCESS') {
            $transaction->update([
                'status' => 'refunded',
                'metadata' => array_merge(
                    $transaction->metadata ?? [],
                    ['refund' => $request->all()]
                ),
            ]);
        }
    }
}
```

---

## 3. RAZORPAY IMPLEMENTATION (FROM JETPAX)

### 3.1 Provider Class

```php
<?php

declare(strict_types=1);

namespace App\Services\Payment\Providers;

use App\Models\Integration;use App\Services\IntegrationServices\Payment\Contracts\PaymentProviderInterface;use App\Services\IntegrationServices\Payment\DTOs\PaymentInitiateRequest;use App\Services\IntegrationServices\Payment\DTOs\PaymentResponse;use App\Services\IntegrationServices\Payment\DTOs\PaymentVerifyRequest;use Razorpay\Api\Api;

final class RazorpayPaymentProvider implements PaymentProviderInterface
{
    private ?Integration $integration = null;
    private ?Api $api = null;

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
        return $this->getIntegration()?->is_active ?? false;
    }

    public function getSupportedMethods(): array
    {
        return ['upi', 'card', 'netbanking', 'wallet'];
    }

    public function initiate(PaymentInitiateRequest $request): PaymentResponse
    {
        $api = $this->getApi();
        if (!$api) {
            return PaymentResponse::failed('Razorpay not configured');
        }

        try {
            $order = $api->order->create([
                'amount' => $request->amount, // Already in paisa
                'currency' => 'INR',
                'receipt' => $request->transactionId,
                'notes' => [
                    'user_id' => $request->userId,
                    'description' => $request->description,
                ],
            ]);

            return PaymentResponse::success(
                transactionId: $request->transactionId,
                providerReference: $order->id,
                metadata: [
                    'razorpay_order_id' => $order->id,
                    'razorpay_key' => $this->getIntegration()->decrypted_credentials['key_id'],
                ]
            );
        } catch (\Exception $e) {
            return PaymentResponse::failed($e->getMessage());
        }
    }

    public function verify(PaymentVerifyRequest $request): PaymentResponse
    {
        $api = $this->getApi();
        if (!$api) {
            return PaymentResponse::failed('Razorpay not configured');
        }

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $request->orderId,
                'razorpay_payment_id' => $request->paymentId,
                'razorpay_signature' => $request->signature,
            ]);

            return PaymentResponse::success(
                transactionId: $request->orderId,
                providerReference: $request->paymentId
            );
        } catch (\Exception $e) {
            return PaymentResponse::failed('Signature verification failed');
        }
    }

    public function refund(string $transactionId, int $amountInPaisa, ?string $reason = null): PaymentResponse
    {
        $api = $this->getApi();
        if (!$api) {
            return PaymentResponse::failed('Razorpay not configured');
        }

        try {
            $refund = $api->refund->create([
                'payment_id' => $transactionId,
                'amount' => $amountInPaisa,
                'notes' => ['reason' => $reason ?? 'Refund requested'],
            ]);

            return PaymentResponse::success(
                transactionId: $transactionId,
                providerReference: $refund->id
            );
        } catch (\Exception $e) {
            return PaymentResponse::failed($e->getMessage());
        }
    }

    private function getApi(): ?Api
    {
        if ($this->api === null) {
            $integration = $this->getIntegration();
            if ($integration) {
                $credentials = $integration->decrypted_credentials;
                $this->api = new Api($credentials['key_id'], $credentials['key_secret']);
            }
        }
        return $this->api;
    }

    private function getIntegration(): ?Integration
    {
        if ($this->integration === null) {
            $this->integration = Integration::where('provider', 'razorpay')
                ->where('type', 'payment')
                ->where('is_active', true)
                ->first();
        }
        return $this->integration;
    }
}
```

---

## 4. INTEGRATION MODEL SETUP

```php
// Store credentials encrypted
Integration::create([
    'provider' => 'cashfree',
    'type' => 'payment', // or 'payout'
    'name' => 'Cashfree Production',
    'credentials' => [ // Will be encrypted
        'app_id' => 'CF_xxx',
        'secret_key' => 'CF_secret_xxx',
        'webhook_secret' => 'CF_webhook_xxx',
        'environment' => 'production', // or 'sandbox'
    ],
    'is_active' => true,
    'is_default' => true,
]);
```

---

## 5. ROUTES SETUP

```php
// routes/api.php

// Webhooks (no auth)
Route::prefix('webhooks')->group(function () {
    Route::post('/cashfree', [CashfreeWebhookController::class, 'handle']);
    Route::post('/razorpay', [RazorpayWebhookController::class, 'handle']);
});

// Payment endpoints (auth required)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/checkout/initiate', [CheckoutController::class, 'initiate']);
    Route::post('/checkout/verify', [CheckoutController::class, 'verify']);
    Route::post('/withdrawal/request', [WithdrawalController::class, 'request']);
});
```

---

## 6. SERVICE PROVIDER REGISTRATION

```php
// app/Providers/PaymentServiceProvider.php

public function register(): void
{
    $this->app->singleton(PaymentService::class, function ($app) {
        $service = new PaymentService(
            $app->make(NativePaymentProvider::class),
            $app->make(NativePayoutProvider::class),
        );

        // Register Cashfree if available
        if (Integration::where('provider', 'cashfree')->where('is_active', true)->exists()) {
            $service->registerPaymentProvider($app->make(CashfreePaymentProvider::class));
            $service->registerPayoutProvider($app->make(CashfreePayoutProvider::class));
        }

        // Register Razorpay if available
        if (Integration::where('provider', 'razorpay')->where('is_active', true)->exists()) {
            $service->registerPaymentProvider($app->make(RazorpayPaymentProvider::class));
            $service->registerPayoutProvider($app->make(RazorpayPayoutProvider::class));
        }

        return $service;
    });
}
```

---

## 7. TESTING CHECKLIST

```
□ Unit Tests:
  □ PaymentInitiateRequest DTO validation
  □ PaymentResponse success/failure states
  □ Commission calculation (5%+4%+3%+2%)

□ Integration Tests (with mocks):
  □ CashfreePaymentProvider->initiate()
  □ CashfreePaymentProvider->verify()
  □ CashfreePayoutProvider->initiate()
  □ Webhook signature verification

□ Feature Tests:
  □ Full subscription checkout flow
  □ Withdrawal request to completion
  □ Refund processing

□ Browser Tests:
  □ Checkout page renders correctly
  □ Payment redirect works
  □ Callback updates UI
```

---

**Last Updated**: 2024-12-14
**Status**: Ready for implementation - DO NOT scan reference projects again
