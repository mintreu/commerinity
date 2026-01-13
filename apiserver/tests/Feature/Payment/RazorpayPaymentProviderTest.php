<?php

declare(strict_types=1);

use App\Casts\PaymentMethodCast;
use App\Models\Integration;
use App\Models\User;
use App\Services\IntegrationServices\Payment\DTOs\PaymentInitiateRequest;
use App\Services\IntegrationServices\Payment\DTOs\PaymentVerifyRequest;
use App\Services\IntegrationServices\Payment\Providers\RazorpayPaymentProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test user
    $this->user = User::factory()->create();

    // Create test integration
    $this->integration = Integration::create([
        'name' => 'Razorpay Test',
        'slug' => 'razorpay',
        'type' => Integration::TYPE_PAYMENT,
        'credentials' => [
            'key_id' => 'rzp_test_key',
            'key_secret' => 'rzp_test_secret',
            'webhook_secret' => 'rzp_webhook_secret',
        ],
        'is_sandbox' => true,
        'is_active' => true,
        'is_default' => false,
    ]);

    $this->provider = new RazorpayPaymentProvider;
});

describe('RazorpayPaymentProvider Configuration', function () {
    it('returns correct slug', function () {
        expect($this->provider->getSlug())->toBe('razorpay');
    });

    it('returns correct name', function () {
        expect($this->provider->getName())->toBe('Razorpay');
    });

    it('is available when integration exists and is active', function () {
        expect($this->provider->isAvailable())->toBeTrue();
    });

    it('is not available when integration is inactive', function () {
        $this->integration->update(['is_active' => false]);
        $this->provider->clearCache();

        expect($this->provider->isAvailable())->toBeFalse();
    });

    it('returns supported payment methods', function () {
        $methods = $this->provider->getSupportedMethods();

        expect($methods)->toContain('razorpay')
            ->toContain('upi')
            ->toContain('card')
            ->toContain('netbanking')
            ->toContain('emi');
    });
});

describe('RazorpayPaymentProvider Initiate', function () {
    it('creates order successfully', function () {
        Http::fake([
            'api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_123456',
                'amount' => 25000,
                'currency' => 'INR',
                'status' => 'created',
                'receipt' => 'TXN-123',
            ], 200),
        ]);

        $request = new PaymentInitiateRequest(
            amountInPaisa: 25000,
            currency: 'INR',
            method: PaymentMethodCast::RAZORPAY,
            userFingerprint: $this->user->fingerprint(),
            userId: $this->user->id,
            walletId: 1,
            transactionId: 'TXN-123',
            customerName: 'Test User',
            customerEmail: 'test@example.com',
            customerPhone: '+919876543210',
            purpose: 'subscription',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('pending')
            ->and($response->transactionId)->toBe('TXN-123')
            ->and($response->providerOrderId)->toBe('order_123456')
            ->and($response->metadata['razorpay_order_id'])->toBe('order_123456')
            ->and($response->metadata['razorpay_key_id'])->toBe('rzp_test_key');
    });

    it('returns failed response on API error', function () {
        Http::fake([
            'api.razorpay.com/v1/orders' => Http::response([
                'error' => [
                    'code' => 'BAD_REQUEST_ERROR',
                    'description' => 'The api key/secret provided is invalid',
                ],
            ], 400),
        ]);

        $request = new PaymentInitiateRequest(
            amountInPaisa: 25000,
            currency: 'INR',
            method: PaymentMethodCast::RAZORPAY,
            userFingerprint: $this->user->fingerprint(),
            userId: $this->user->id,
            walletId: 1,
            transactionId: 'TXN-124',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->status)->toBe('failed');
    });

    it('returns failed response when not configured', function () {
        $this->integration->delete();
        $this->provider->clearCache();

        $request = new PaymentInitiateRequest(
            amountInPaisa: 25000,
            currency: 'INR',
            method: PaymentMethodCast::RAZORPAY,
            userFingerprint: $this->user->fingerprint(),
            userId: $this->user->id,
            walletId: 1,
            transactionId: 'TXN-125',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Razorpay not configured');
    });
});

describe('RazorpayPaymentProvider Verify', function () {
    it('verifies payment with valid signature', function () {
        // Generate valid signature
        $orderId = 'order_123';
        $paymentId = 'pay_456';
        $secret = 'rzp_test_secret';
        $signature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

        Http::fake([
            'api.razorpay.com/v1/payments/pay_456' => Http::response([
                'id' => 'pay_456',
                'order_id' => 'order_123',
                'status' => 'captured',
                'amount' => 25000,
            ], 200),
        ]);

        $request = new PaymentVerifyRequest(
            orderId: 'TXN-123',
            providerOrderId: $orderId,
            providerTransactionId: $paymentId,
            providerSignature: $signature,
        );

        $response = $this->provider->verify($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('completed');
    });

    it('rejects payment with invalid signature', function () {
        $request = new PaymentVerifyRequest(
            orderId: 'TXN-123',
            providerOrderId: 'order_123',
            providerTransactionId: 'pay_456',
            providerSignature: 'invalid_signature',
        );

        $response = $this->provider->verify($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Signature verification failed');
    });

    it('requires signature for verification', function () {
        $request = new PaymentVerifyRequest(
            orderId: 'TXN-123',
            providerOrderId: 'order_123',
            providerTransactionId: 'pay_456',
            // No signature
        );

        $response = $this->provider->verify($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Signature required for verification');
    });

    it('returns pending for authorized but not captured payment', function () {
        $orderId = 'order_123';
        $paymentId = 'pay_456';
        $secret = 'rzp_test_secret';
        $signature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

        Http::fake([
            'api.razorpay.com/v1/payments/pay_456' => Http::response([
                'id' => 'pay_456',
                'order_id' => 'order_123',
                'status' => 'authorized', // Not captured yet
                'amount' => 25000,
            ], 200),
        ]);

        $request = new PaymentVerifyRequest(
            orderId: 'TXN-123',
            providerOrderId: $orderId,
            providerTransactionId: $paymentId,
            providerSignature: $signature,
        );

        $response = $this->provider->verify($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('pending');
    });
});

describe('RazorpayPaymentProvider Refund', function () {
    it('initiates refund successfully', function () {
        Http::fake([
            'api.razorpay.com/v1/payments/pay_123/refund' => Http::response([
                'id' => 'rfnd_789',
                'payment_id' => 'pay_123',
                'amount' => 25000,
                'status' => 'processed',
            ], 200),
        ]);

        $response = $this->provider->refund('pay_123', 25000, 'Customer request');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('processing')
            ->and($response->providerTransactionId)->toBe('rfnd_789');
    });

    it('returns failed for invalid refund', function () {
        Http::fake([
            'api.razorpay.com/v1/payments/pay_123/refund' => Http::response([
                'error' => [
                    'description' => 'Payment has already been refunded',
                ],
            ], 400),
        ]);

        $response = $this->provider->refund('pay_123', 25000);

        expect($response->success)->toBeFalse();
    });
});

describe('RazorpayPaymentProvider Webhook Signature', function () {
    it('verifies valid webhook signature', function () {
        $rawBody = '{"event":"payment.captured","payload":{}}';
        $webhookSecret = 'rzp_webhook_secret';
        $signature = hash_hmac('sha256', $rawBody, $webhookSecret);

        $result = $this->provider->verifyWebhookSignature($rawBody, $signature);

        expect($result)->toBeTrue();
    });

    it('rejects invalid webhook signature', function () {
        $rawBody = '{"event":"payment.captured","payload":{}}';

        $result = $this->provider->verifyWebhookSignature($rawBody, 'invalid_signature');

        expect($result)->toBeFalse();
    });
});

describe('RazorpayPaymentProvider Fetch Operations', function () {
    it('fetches payment details', function () {
        Http::fake([
            'api.razorpay.com/v1/payments/pay_123' => Http::response([
                'id' => 'pay_123',
                'amount' => 25000,
                'status' => 'captured',
            ], 200),
        ]);

        $result = $this->provider->fetchPayment('pay_123');

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('pay_123');
    });

    it('returns null for non-existent payment', function () {
        Http::fake([
            'api.razorpay.com/v1/payments/pay_invalid' => Http::response([
                'error' => ['description' => 'Payment not found'],
            ], 404),
        ]);

        $result = $this->provider->fetchPayment('pay_invalid');

        expect($result)->toBeNull();
    });

    it('fetches order details', function () {
        Http::fake([
            'api.razorpay.com/v1/orders/order_123' => Http::response([
                'id' => 'order_123',
                'amount' => 25000,
                'status' => 'paid',
            ], 200),
        ]);

        $result = $this->provider->fetchOrder('order_123');

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('order_123');
    });
});
