<?php

declare(strict_types=1);

use App\Models\Integration;
use App\Services\Payment\Providers\CashfreePayoutProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test payout integration
    $this->integration = Integration::create([
        'name' => 'Cashfree Payout Test',
        'slug' => 'cashfree',
        'type' => Integration::TYPE_PAYOUT,
        'credentials' => [
            'app_id' => 'test_payout_app_id',
            'secret_key' => 'test_payout_secret_key',
        ],
        'is_sandbox' => true,
        'is_active' => true,
        'is_default' => true,
    ]);

    $this->provider = new CashfreePayoutProvider;
});

describe('CashfreePayoutProvider Configuration', function () {
    it('returns correct slug', function () {
        expect($this->provider->getSlug())->toBe('cashfree');
    });

    it('returns correct name', function () {
        expect($this->provider->getName())->toBe('Cashfree Payouts');
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

describe('CashfreePayoutProvider Status Check', function () {
    it('checks transfer status successfully - completed', function () {
        Http::fake([
            'payout-gamma.cashfree.com/payout/v1/authorize' => Http::response([
                'status' => 'SUCCESS',
                'data' => ['token' => 'test_token', 'expiry' => time() + 300],
            ], 200),
            'payout-gamma.cashfree.com/payout/v1/getTransferStatus*' => Http::response([
                'status' => 'SUCCESS',
                'subCode' => '200',
                'data' => [
                    'transfer' => [
                        'transferId' => 'TXN-123',
                        'status' => 'SUCCESS',
                        'utr' => 'UTR123456789',
                        'referenceId' => 'cf_ref_123',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->provider->checkStatus('TXN-123');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('completed');
    });

    it('handles pending transfer status', function () {
        Http::fake([
            'payout-gamma.cashfree.com/payout/v1/authorize' => Http::response([
                'status' => 'SUCCESS',
                'data' => ['token' => 'test_token', 'expiry' => time() + 300],
            ], 200),
            'payout-gamma.cashfree.com/payout/v1/getTransferStatus*' => Http::response([
                'status' => 'SUCCESS',
                'subCode' => '200',
                'data' => [
                    'transfer' => [
                        'transferId' => 'TXN-123',
                        'status' => 'PENDING',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->provider->checkStatus('TXN-123');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('processing');
    });

    it('handles failed transfer status', function () {
        Http::fake([
            'payout-gamma.cashfree.com/payout/v1/authorize' => Http::response([
                'status' => 'SUCCESS',
                'data' => ['token' => 'test_token', 'expiry' => time() + 300],
            ], 200),
            'payout-gamma.cashfree.com/payout/v1/getTransferStatus*' => Http::response([
                'status' => 'SUCCESS',
                'subCode' => '200',
                'data' => [
                    'transfer' => [
                        'transferId' => 'TXN-123',
                        'status' => 'FAILED',
                        'reason' => 'Invalid account details',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->provider->checkStatus('TXN-123');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('failed');
    });

    it('returns failed when integration not configured', function () {
        $this->integration->delete();
        $this->provider->clearCache();

        $response = $this->provider->checkStatus('TXN-123');

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Cashfree Payouts not configured');
    });
});

describe('CashfreePayoutProvider Balance', function () {
    it('gets balance successfully', function () {
        Http::fake([
            'payout-gamma.cashfree.com/payout/v1/authorize' => Http::response([
                'status' => 'SUCCESS',
                'data' => ['token' => 'test_token', 'expiry' => time() + 300],
            ], 200),
            'payout-gamma.cashfree.com/payout/v1/getBalance' => Http::response([
                'status' => 'SUCCESS',
                'subCode' => '200',
                'data' => [
                    'balance' => 50000.50,
                    'availableBalance' => 45000.00,
                ],
            ], 200),
        ]);

        $result = $this->provider->getBalance();

        expect($result)->toBeArray()
            ->and($result['balance'])->toBe(50000.50)
            ->and((float) $result['availableBalance'])->toBe(45000.00);
    });

    it('returns null when balance fetch fails', function () {
        Http::fake([
            'payout-gamma.cashfree.com/payout/v1/authorize' => Http::response([
                'status' => 'SUCCESS',
                'data' => ['token' => 'test_token', 'expiry' => time() + 300],
            ], 200),
            'payout-gamma.cashfree.com/payout/v1/getBalance' => Http::response([
                'status' => 'ERROR',
                'message' => 'Unauthorized',
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
});

describe('CashfreePayoutProvider Remove Beneficiary', function () {
    it('removes beneficiary successfully', function () {
        Http::fake([
            'payout-gamma.cashfree.com/payout/v1/authorize' => Http::response([
                'status' => 'SUCCESS',
                'data' => ['token' => 'test_token', 'expiry' => time() + 300],
            ], 200),
            'payout-gamma.cashfree.com/payout/v1/removeBeneficiary' => Http::response([
                'status' => 'SUCCESS',
                'subCode' => '200',
                'message' => 'Beneficiary removed',
            ], 200),
        ]);

        $result = $this->provider->removeBeneficiary('BENE-123');

        expect($result)->toBeTrue();
    });

    it('returns false when remove fails', function () {
        Http::fake([
            'payout-gamma.cashfree.com/payout/v1/authorize' => Http::response([
                'status' => 'SUCCESS',
                'data' => ['token' => 'test_token', 'expiry' => time() + 300],
            ], 200),
            'payout-gamma.cashfree.com/payout/v1/removeBeneficiary' => Http::response([
                'status' => 'ERROR',
                'message' => 'Beneficiary not found',
            ], 404),
        ]);

        $result = $this->provider->removeBeneficiary('BENE-NOTFOUND');

        expect($result)->toBeFalse();
    });

    it('returns false when integration not configured', function () {
        $this->integration->delete();
        $this->provider->clearCache();

        $result = $this->provider->removeBeneficiary('BENE-123');

        expect($result)->toBeFalse();
    });
});
