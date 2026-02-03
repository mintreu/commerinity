<?php

declare(strict_types=1);

use App\Casts\PaymentMethodCast;
use App\Casts\IntegrationTypeCast;
use App\Models\Integration;
use App\Models\User;
use App\Services\IntegrationServices\Payment\DTOs\PaymentInitiateRequest;
use App\Services\IntegrationServices\Payment\DTOs\PaymentVerifyRequest;
use App\Services\IntegrationServices\Payment\Providers\Cashfree\CashfreePaymentProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test user
    $this->user = User::factory()->create();

    // Create test integration
    $this->integration = Integration::create([
        'name' => 'Cashfree Test',
        'slug' => 'cashfree',
        'type' => IntegrationTypeCast::PAYMENT->value,
        'credentials' => [
            'app_id' => 'test_app_id',
            'secret_key' => 'test_secret_key',
            'webhook_secret' => 'test_webhook_secret',
        ],
        'is_sandbox' => true,
        'is_active' => true,
        'is_default' => true,
    ]);

    $this->provider = new CashfreePaymentProvider;
});

describe('CashfreePaymentProvider Configuration', function () {
    it('returns correct slug', function () {
        expect($this->provider->getSlug())->toBe('cashfree');
    });

    it('returns correct name', function () {
        expect($this->provider->getName())->toBe('Cashfree Payment Gateway');
    });

    it('is available when integration exists and is active', function () {
        expect($this->provider->isAvailable())->toBeTrue();
    });

    it('is not available when integration is inactive', function () {
        $this->integration->update(['is_active' => false]);
        $this->provider->clearCache();

        expect($this->provider->isAvailable())->toBeFalse();
    });

    it('is not available when no integration exists', function () {
        $this->integration->delete();
        $this->provider->clearCache();

        expect($this->provider->isAvailable())->toBeFalse();
    });

    it('returns supported payment methods', function () {
        $methods = $this->provider->getSupportedMethods();

        expect($methods)->toContain('cashfree')
            ->toContain('upi')
            ->toContain('card')
            ->toContain('netbanking');
    });
});

describe('CashfreePaymentProvider Initiate', function () {
    it('creates order successfully', function () {
        Http::fake([
            'sandbox.cashfree.com/pg/orders' => Http::response([
                'cf_order_id' => 'cf_order_123',
                'order_id' => 'TXN-123',
                'payment_session_id' => 'session_123',
                'order_status' => 'ACTIVE',
            ], 200),
        ]);

        $request = new PaymentInitiateRequest(
            amountInPaisa: 25000, // Rs 250
            currency: 'INR',
            method: PaymentMethodCast::CASHFREE,
            userFingerprint: $this->user->fingerprint(),
            userId: $this->user->id,
            walletId: 1,
            transactionId: 'TXN-123',
            customerName: 'Test User',
            customerEmail: 'test@example.com',
            customerPhone: '+919876543210',
            purpose: 'subscription',
            description: 'Test payment',
            callbackUrl: 'https://example.com/callback',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('pending')
            ->and($response->transactionId)->toBe('TXN-123')
            ->and($response->providerOrderId)->toBe('cf_order_123')
            ->and($response->metadata['payment_session_id'])->toBe('session_123');
    });

    it('returns failed response on API error', function () {
        Http::fake([
            'sandbox.cashfree.com/pg/orders' => Http::response([
                'message' => 'Invalid credentials',
                'code' => 'INVALID_AUTH',
            ], 401),
        ]);

        $request = new PaymentInitiateRequest(
            amountInPaisa: 25000,
            currency: 'INR',
            method: PaymentMethodCast::CASHFREE,
            userFingerprint: $this->user->fingerprint(),
            userId: $this->user->id,
            walletId: 1,
            transactionId: 'TXN-124',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->status)->toBe('failed')
            ->and($response->message)->toContain('Invalid credentials');
    });

    it('returns failed response when not configured', function () {
        $this->integration->delete();
        $this->provider->clearCache();

        $request = new PaymentInitiateRequest(
            amountInPaisa: 25000,
            currency: 'INR',
            method: PaymentMethodCast::CASHFREE,
            userFingerprint: $this->user->fingerprint(),
            userId: $this->user->id,
            walletId: 1,
            transactionId: 'TXN-125',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Cashfree not configured');
    });
});

describe('CashfreePaymentProvider Verify', function () {
    it('verifies successful payment', function () {
        Http::fake([
            'sandbox.cashfree.com/pg/orders/TXN-123' => Http::response([
                'cf_order_id' => 'cf_order_123',
                'order_id' => 'TXN-123',
                'order_status' => 'PAID',
                'cf_payment_id' => 'cf_payment_456',
            ], 200),
        ]);

        $request = new PaymentVerifyRequest(
            orderId: 'TXN-123',
            providerOrderId: 'cf_order_123',
        );

        $response = $this->provider->verify($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('completed')
            ->and($response->transactionId)->toBe('TXN-123');
    });

    it('returns pending for active payment', function () {
        Http::fake([
            'sandbox.cashfree.com/pg/orders/TXN-123' => Http::response([
                'cf_order_id' => 'cf_order_123',
                'order_id' => 'TXN-123',
                'order_status' => 'ACTIVE',
            ], 200),
        ]);

        $request = new PaymentVerifyRequest(
            orderId: 'TXN-123',
        );

        $response = $this->provider->verify($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('pending');
    });

    it('returns failed for expired/cancelled payment', function () {
        Http::fake([
            'sandbox.cashfree.com/pg/orders/TXN-123' => Http::response([
                'cf_order_id' => 'cf_order_123',
                'order_id' => 'TXN-123',
                'order_status' => 'EXPIRED',
            ], 200),
        ]);

        $request = new PaymentVerifyRequest(
            orderId: 'TXN-123',
        );

        $response = $this->provider->verify($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toContain('EXPIRED');
    });
});

describe('CashfreePaymentProvider Refund', function () {
    it('initiates refund successfully', function () {
        Http::fake([
            'sandbox.cashfree.com/pg/orders/TXN-123/refunds' => Http::response([
                'cf_refund_id' => 'cf_refund_789',
                'refund_status' => 'PENDING',
            ], 200),
        ]);

        $response = $this->provider->refund('TXN-123', 25000, 'Customer request');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('processing')
            ->and($response->message)->toBe('Refund initiated');
    });

    it('returns failed for invalid refund', function () {
        Http::fake([
            'sandbox.cashfree.com/pg/orders/TXN-123/refunds' => Http::response([
                'message' => 'Order not found or not paid',
            ], 400),
        ]);

        $response = $this->provider->refund('TXN-123', 25000);

        expect($response->success)->toBeFalse();
    });
});

describe('CashfreePaymentProvider Webhook Signature', function () {
    it('verifies valid signature', function () {
        $timestamp = (string) time();
        $rawBody = '{"type":"PAYMENT_SUCCESS_WEBHOOK","data":{}}';
        $webhookSecret = 'test_webhook_secret';

        $signature = base64_encode(
            hash_hmac('sha256', $timestamp.$rawBody, $webhookSecret, true)
        );

        $result = $this->provider->verifyWebhookSignature($timestamp, $rawBody, $signature);

        expect($result)->toBeTrue();
    });

    it('rejects invalid signature', function () {
        $timestamp = (string) time();
        $rawBody = '{"type":"PAYMENT_SUCCESS_WEBHOOK","data":{}}';

        $result = $this->provider->verifyWebhookSignature($timestamp, $rawBody, 'invalid_signature');

        expect($result)->toBeFalse();
    });

    it('rejects when webhook secret not configured', function () {
        $this->integration->update(['credentials' => [
            'app_id' => 'test_app_id',
            'secret_key' => 'test_secret_key',
            // No webhook_secret
        ]]);
        $this->provider->clearCache();

        $result = $this->provider->verifyWebhookSignature('123', 'body', 'sig');

        expect($result)->toBeFalse();
    });
});
