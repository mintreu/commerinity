<?php

declare(strict_types=1);

use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Models\Integration;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\IntegrationServices\Payment\DTOs\PaymentInitiateRequest;
use App\Services\IntegrationServices\Payment\PaymentRetryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create user and wallet
    $this->user = User::factory()->create();
    $this->wallet = Wallet::factory()->forUser($this->user)->withBalance(500000)->create(); // Rs 5000

    // Create payment integration for external provider
    $this->integration = Integration::create([
        'name' => 'Cashfree Test',
        'slug' => 'cashfree',
        'type' => Integration::TYPE_PAYMENT,
        'credentials' => [
            'app_id' => 'test_app_id',
            'secret_key' => 'test_secret_key',
        ],
        'is_sandbox' => true,
        'is_active' => true,
        'is_default' => true,
    ]);

    // Create pending transaction
    $this->transaction = Transaction::create([
        'uuid' => 'TXN-RETRY-001',
        'wallet_id' => $this->wallet->id,
        'type' => 'credit',
        'amount' => 100000, // Rs 1000
        'status' => TransactionStatusCast::PENDING,
        'payment_method' => PaymentMethodCast::CASHFREE,
        'provider_order_id' => 'cf_order_123',
        'expires_at' => now()->subMinutes(5), // Expired 5 minutes ago
        'transactionable_type' => \App\Models\Wallet::class,
        'transactionable_id' => $this->wallet->id,
        'metadata' => [
            'provider_order_created_at' => now()->subMinutes(35)->toIso8601String(),
            'provider_order_expiry_minutes' => 30,
        ],
    ]);

    $this->retryService = app(PaymentRetryService::class);

    // Clear any existing rate limits
    Cache::flush();
});

describe('PaymentRetryService Expiry Check', function () {
    it('detects expired transaction by expires_at', function () {
        expect($this->retryService->isProviderOrderExpired($this->transaction))->toBeTrue();
    });

    it('detects expired transaction by provider order age', function () {
        $this->transaction->update([
            'expires_at' => null,
            'metadata' => [
                'provider_order_created_at' => now()->subMinutes(35)->toIso8601String(),
                'provider_order_expiry_minutes' => 30,
            ],
        ]);

        expect($this->retryService->isProviderOrderExpired($this->transaction))->toBeTrue();
    });

    it('detects non-expired transaction', function () {
        $this->transaction->update([
            'expires_at' => now()->addMinutes(10),
            'metadata' => [
                'provider_order_created_at' => now()->subMinutes(5)->toIso8601String(),
                'provider_order_expiry_minutes' => 30,
            ],
        ]);

        expect($this->retryService->isProviderOrderExpired($this->transaction))->toBeFalse();
    });

    it('detects old pending transaction as expired', function () {
        // Transaction older than 30 min without explicit expiry
        // Need to use fresh model to properly test created_at
        $oldTransaction = Transaction::create([
            'uuid' => 'TXN-OLD-001',
            'wallet_id' => $this->wallet->id,
            'type' => 'credit',
            'amount' => 100000,
            'status' => TransactionStatusCast::PENDING,
            'payment_method' => PaymentMethodCast::CASHFREE,
            'transactionable_type' => \App\Models\Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'metadata' => [],
        ]);

        // Directly update created_at in database to bypass Eloquent timestamp handling
        \DB::table('transactions')
            ->where('id', $oldTransaction->id)
            ->update(['created_at' => now()->subMinutes(45), 'expires_at' => null]);

        // Refresh to get updated values
        $oldTransaction->refresh();

        expect($this->retryService->isProviderOrderExpired($oldTransaction))->toBeTrue();
    });
});

describe('PaymentRetryService Eligibility', function () {
    it('allows retry for pending transaction', function () {
        $status = $this->retryService->getRetryStatus($this->transaction);

        expect($status['can_retry'])->toBeTrue();
    });

    it('allows retry for failed transaction', function () {
        $this->transaction->update(['status' => TransactionStatusCast::FAILED]);

        $status = $this->retryService->getRetryStatus($this->transaction);

        expect($status['can_retry'])->toBeTrue();
    });

    it('allows retry for cancelled transaction', function () {
        $this->transaction->update(['status' => TransactionStatusCast::CANCELLED]);

        $status = $this->retryService->getRetryStatus($this->transaction);

        expect($status['can_retry'])->toBeTrue();
    });

    it('allows retry for expired transaction', function () {
        $this->transaction->update(['status' => TransactionStatusCast::EXPIRED]);

        $status = $this->retryService->getRetryStatus($this->transaction);

        expect($status['can_retry'])->toBeTrue();
    });

    it('denies retry for completed transaction', function () {
        $this->transaction->update(['status' => TransactionStatusCast::COMPLETED]);

        $status = $this->retryService->getRetryStatus($this->transaction);

        expect($status['can_retry'])->toBeFalse()
            ->and($status['reason'])->toBe('Transaction is already completed');
    });

    it('denies retry for refunded transaction', function () {
        $this->transaction->update(['status' => TransactionStatusCast::REFUNDED]);

        $status = $this->retryService->getRetryStatus($this->transaction);

        expect($status['can_retry'])->toBeFalse()
            ->and($status['reason'])->toBe('Transaction has been refunded');
    });

    it('denies retry when wallet is suspended', function () {
        $this->wallet->suspend();

        $status = $this->retryService->getRetryStatus($this->transaction);

        expect($status['can_retry'])->toBeFalse()
            ->and($status['reason'])->toBe('Wallet is not active');
    });
});

describe('PaymentRetryService Rate Limiting', function () {
    it('allows first retry attempt', function () {
        $status = $this->retryService->getRetryStatus($this->transaction);

        expect($status['can_retry'])->toBeTrue()
            ->and($status['retry_count'])->toBe(0);
    });

    it('blocks retry during cooldown period', function () {
        // Simulate a recent retry
        Cache::put("payment_retry_cooldown:{$this->transaction->uuid}", time() + 30, 30);

        $status = $this->retryService->getRetryStatus($this->transaction);

        expect($status['can_retry'])->toBeFalse()
            ->and($status['reason'])->toContain('Please wait');
    });

    it('blocks retry after max attempts per transaction', function () {
        // Simulate max retries reached
        Cache::put("payment_retry:{$this->transaction->uuid}", 5, 3600);

        $status = $this->retryService->getRetryStatus($this->transaction);

        expect($status['can_retry'])->toBeFalse()
            ->and($status['reason'])->toContain('Maximum retry attempts reached');
    });

    it('blocks retry after max attempts per user', function () {
        // Load wallet relationship to ensure user_id is available
        $this->transaction->load('wallet');

        // Simulate max user retries reached
        Cache::put("payment_retry_user:{$this->wallet->user_id}", 10, 3600);

        $status = $this->retryService->getRetryStatus($this->transaction);

        expect($status['can_retry'])->toBeFalse()
            ->and($status['reason'])->toContain('Maximum payment attempts reached');
    });

    it('tracks retry count in transaction metadata', function () {
        Http::fake([
            'sandbox.cashfree.com/pg/orders/*' => Http::response([
                'order_status' => 'EXPIRED',
            ], 200),
            'sandbox.cashfree.com/pg/orders' => Http::response([
                'cf_order_id' => 'cf_new_order_123',
                'payment_session_id' => 'session_123',
                'payment_link' => 'https://payments.cashfree.com/pay/123',
                'order_status' => 'ACTIVE',
            ], 200),
        ]);

        $request = new PaymentInitiateRequest(
            amountInPaisa: 100000,
            currency: 'INR',
            method: PaymentMethodCast::CASHFREE,
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: $this->transaction->uuid,
        );

        $this->retryService->retryPayment($this->transaction, $request, 'cashfree');

        $this->transaction->refresh();
        expect($this->transaction->metadata['retry_count'])->toBe(1)
            ->and($this->transaction->metadata['last_retry_at'])->not->toBeNull();
    });
});

describe('PaymentRetryService Retry Flow', function () {
    it('retries payment with new provider order', function () {
        Http::fake([
            'sandbox.cashfree.com/pg/orders/cf_order_123' => Http::response([
                'order_status' => 'EXPIRED',
            ], 200),
            'sandbox.cashfree.com/pg/orders' => Http::response([
                'cf_order_id' => 'cf_new_order_456',
                'payment_session_id' => 'session_456',
                'payment_link' => 'https://payments.cashfree.com/pay/456',
                'order_status' => 'ACTIVE',
            ], 200),
        ]);

        $request = new PaymentInitiateRequest(
            amountInPaisa: 100000,
            currency: 'INR',
            method: PaymentMethodCast::CASHFREE,
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: $this->transaction->uuid,
            customerPhone: '9876543210',
        );

        $response = $this->retryService->retryPayment($this->transaction, $request, 'cashfree');

        expect($response->success)->toBeTrue()
            ->and($response->providerOrderId)->toBe('cf_new_order_456');

        $this->transaction->refresh();
        expect($this->transaction->provider_order_id)->toBe('cf_new_order_456')
            ->and($this->transaction->status)->toBe(TransactionStatusCast::PENDING)
            ->and($this->transaction->metadata['previous_attempts'])->toHaveCount(1);
    });

    it('stores previous attempt in metadata', function () {
        Http::fake([
            'sandbox.cashfree.com/pg/orders/*' => Http::response([
                'order_status' => 'EXPIRED',
            ], 200),
            'sandbox.cashfree.com/pg/orders' => Http::response([
                'cf_order_id' => 'cf_new_order_789',
                'payment_session_id' => 'session_789',
                'payment_link' => 'https://payments.cashfree.com/pay/789',
                'order_status' => 'ACTIVE',
            ], 200),
        ]);

        $oldOrderId = $this->transaction->provider_order_id;

        $request = new PaymentInitiateRequest(
            amountInPaisa: 100000,
            currency: 'INR',
            method: PaymentMethodCast::CASHFREE,
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: $this->transaction->uuid,
        );

        $this->retryService->retryPayment($this->transaction, $request, 'cashfree');

        $this->transaction->refresh();
        $previousAttempts = $this->transaction->metadata['previous_attempts'] ?? [];

        expect($previousAttempts)->toHaveCount(1)
            ->and($previousAttempts[0]['provider_order_id'])->toBe($oldOrderId)
            // Status is 'expired' because handleExpiredProviderOrder() marks expired transactions before storing previous attempt
            ->and($previousAttempts[0]['status'])->toBe('expired');
    });

    it('fails retry for completed transaction', function () {
        $this->transaction->update(['status' => TransactionStatusCast::COMPLETED]);

        $request = new PaymentInitiateRequest(
            amountInPaisa: 100000,
            currency: 'INR',
            method: PaymentMethodCast::CASHFREE,
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: $this->transaction->uuid,
        );

        $response = $this->retryService->retryPayment($this->transaction, $request, 'cashfree');

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Transaction is already completed');
    });

    it('fails retry when rate limited', function () {
        Cache::put("payment_retry:{$this->transaction->uuid}", 5, 3600);

        $request = new PaymentInitiateRequest(
            amountInPaisa: 100000,
            currency: 'INR',
            method: PaymentMethodCast::CASHFREE,
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: $this->transaction->uuid,
        );

        $response = $this->retryService->retryPayment($this->transaction, $request, 'cashfree');

        expect($response->success)->toBeFalse()
            ->and($response->message)->toContain('Maximum retry attempts');
    });
});

describe('PaymentRetryService Admin Functions', function () {
    it('clears rate limit for transaction', function () {
        Cache::put("payment_retry:{$this->transaction->uuid}", 5, 3600);
        Cache::put("payment_retry_cooldown:{$this->transaction->uuid}", time() + 30, 30);

        $this->retryService->clearRateLimit($this->transaction);

        expect(Cache::has("payment_retry:{$this->transaction->uuid}"))->toBeFalse()
            ->and(Cache::has("payment_retry_cooldown:{$this->transaction->uuid}"))->toBeFalse();
    });

    it('clears rate limit for user', function () {
        Cache::put("payment_retry_user:{$this->user->id}", 10, 3600);

        $this->retryService->clearUserRateLimit($this->user->id);

        expect(Cache::has("payment_retry_user:{$this->user->id}"))->toBeFalse();
    });
});

describe('PaymentRetryService Native Provider', function () {
    it('retries native payment successfully', function () {
        // Create a native payment transaction
        $nativeTransaction = Transaction::create([
            'uuid' => 'TXN-NATIVE-RETRY',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'amount' => 50000,
            'status' => TransactionStatusCast::FAILED,
            'payment_method' => PaymentMethodCast::WALLET,
            'transactionable_type' => \App\Models\Wallet::class,
            'transactionable_id' => $this->wallet->id,
        ]);

        $request = new PaymentInitiateRequest(
            amountInPaisa: 50000,
            currency: 'INR',
            method: PaymentMethodCast::WALLET,
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: $nativeTransaction->uuid,
        );

        $response = $this->retryService->retryPayment($nativeTransaction, $request, 'native');

        expect($response->success)->toBeTrue();
    });
});

describe('PaymentRetryService Status', function () {
    it('returns complete retry status', function () {
        $this->transaction->update([
            'metadata' => array_merge($this->transaction->metadata ?? [], [
                'retry_count' => 2,
            ]),
        ]);

        $status = $this->retryService->getRetryStatus($this->transaction);

        expect($status)->toHaveKeys([
            'can_retry',
            'reason',
            'retry_count',
            'max_retries',
            'next_retry_at',
            'is_expired',
            'expires_at',
        ])
            ->and($status['retry_count'])->toBe(2)
            ->and($status['max_retries'])->toBe(5)
            ->and($status['is_expired'])->toBeTrue();
    });
});
