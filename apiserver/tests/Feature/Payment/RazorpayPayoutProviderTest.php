<?php

declare(strict_types=1);

use App\Casts\IntegrationTypeCast;
use App\Models\Integration;
use App\Services\IntegrationServices\Payout\Providers\RazorpayPayoutProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test payout integration (RazorpayX)
    $this->integration = Integration::create([
        'name' => 'RazorpayX Test',
        'slug' => 'razorpay',
        'type' => IntegrationTypeCast::PAYOUT->value,
        'credentials' => [
            'key_id' => 'rzp_test_key',
            'key_secret' => 'rzp_test_secret',
            'account_number' => '1234567890123456',
        ],
        'is_sandbox' => true,
        'is_active' => true,
        'is_default' => true,
    ]);

    $this->provider = new RazorpayPayoutProvider;
});

describe('RazorpayPayoutProvider Configuration', function () {
    it('returns correct slug', function () {
        expect($this->provider->getSlug())->toBe('razorpay');
    });

    it('returns correct name', function () {
        expect($this->provider->getName())->toBe('RazorpayX Payouts');
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

    it('returns supported payout methods', function () {
        $methods = $this->provider->getSupportedMethods();

        expect($methods)->toContain('bank_transfer')
            ->toContain('upi');
    });
});

describe('RazorpayPayoutProvider Status Check', function () {
    it('checks payout status successfully - processed', function () {
        Http::fake([
            'api.razorpay.com/v1/payouts/pout_123' => Http::response([
                'id' => 'pout_123',
                'entity' => 'payout',
                'status' => 'processed',
                'utr' => 'UTR123456789',
                'reference_id' => 'TXN-123',
            ], 200),
        ]);

        $response = $this->provider->checkStatus('pout_123');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('completed');
    });

    it('handles pending payout status', function () {
        Http::fake([
            'api.razorpay.com/v1/payouts/pout_123' => Http::response([
                'id' => 'pout_123',
                'entity' => 'payout',
                'status' => 'processing',
                'reference_id' => 'TXN-123',
            ], 200),
        ]);

        $response = $this->provider->checkStatus('pout_123');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('processing');
    });

    it('handles failed payout status', function () {
        Http::fake([
            'api.razorpay.com/v1/payouts/pout_123' => Http::response([
                'id' => 'pout_123',
                'entity' => 'payout',
                'status' => 'failed',
                'failure_reason' => 'Account frozen',
                'reference_id' => 'TXN-123',
            ], 200),
        ]);

        $response = $this->provider->checkStatus('pout_123');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('failed');
    });

    it('handles reversed payout status', function () {
        Http::fake([
            'api.razorpay.com/v1/payouts/pout_123' => Http::response([
                'id' => 'pout_123',
                'entity' => 'payout',
                'status' => 'reversed',
                'reference_id' => 'TXN-123',
            ], 200),
        ]);

        $response = $this->provider->checkStatus('pout_123');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('reversed');
    });

    it('returns failed when integration not configured', function () {
        $this->integration->delete();
        $this->provider->clearCache();

        $response = $this->provider->checkStatus('pout_123');

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('RazorpayX Payouts not configured');
    });
});

describe('RazorpayPayoutProvider Balance', function () {
    it('gets balance successfully', function () {
        Http::fake([
            'api.razorpay.com/v1/balance/1234567890123456' => Http::response([
                'id' => 'bal_123',
                'entity' => 'balance',
                'balance' => 5000000,
                'currency' => 'INR',
            ], 200),
        ]);

        $result = $this->provider->getBalance();

        expect($result)->toBeArray()
            ->and($result['balance'])->toBe(5000000)
            ->and($result['currency'])->toBe('INR');
    });

    it('returns null when balance fetch fails', function () {
        Http::fake([
            'api.razorpay.com/v1/balance/*' => Http::response([
                'error' => ['description' => 'Unauthorized'],
            ], 401),
        ]);

        $result = $this->provider->getBalance();

        expect($result)->toBeNull();
    });

    it('returns null when integration not configured', function () {
        $this->integration->delete();
        $this->provider->clearCache();

        $result = $this->provider->getBalance();

        expect($result)->toBeNull();
    });

    it('returns null when account number not configured', function () {
        $this->integration->update(['credentials' => [
            'key_id' => 'rzp_test_key',
            'key_secret' => 'rzp_test_secret',
            // No account_number
        ]]);
        $this->provider->clearCache();

        $result = $this->provider->getBalance();

        expect($result)->toBeNull();
    });
});

describe('RazorpayPayoutProvider Cancel', function () {
    it('cancels pending payout successfully', function () {
        Http::fake([
            'api.razorpay.com/v1/payouts/pout_123/cancel' => Http::response([
                'id' => 'pout_123',
                'entity' => 'payout',
                'status' => 'cancelled',
            ], 200),
        ]);

        $response = $this->provider->cancel('pout_123');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('cancelled');
    });

    it('handles cancel failure for processed payout', function () {
        Http::fake([
            'api.razorpay.com/v1/payouts/pout_123/cancel' => Http::response([
                'error' => [
                    'code' => 'BAD_REQUEST_ERROR',
                    'description' => 'Payout has already been processed',
                ],
            ], 400),
        ]);

        $response = $this->provider->cancel('pout_123');

        expect($response->success)->toBeFalse();
    });

    it('returns failed when integration not configured', function () {
        $this->integration->delete();
        $this->provider->clearCache();

        $response = $this->provider->cancel('pout_123');

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('RazorpayX not configured');
    });
});
