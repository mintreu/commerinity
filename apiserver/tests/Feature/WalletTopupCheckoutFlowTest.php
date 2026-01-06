<?php

declare(strict_types=1);

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Wallet Topup & Checkout Flow Integration Test
 *
 * Tests the complete end-to-end flow:
 * 1. User initiates wallet topup
 * 2. Transaction is created
 * 3. Checkout page loads
 * 4. Payment is processed (mocked)
 * 5. Webhook updates transaction
 * 6. Wallet balance is updated
 * 7. Notification is sent
 */

test('user can initiate wallet topup and get checkout url', function () {
    // Create Cashfree integration for testing
    \App\Models\Integration::factory()->cashfree()->create();
    app(\App\Services\IntegrationServices\Payment\PaymentService::class)->refreshProviders();

    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create();

    // Mock Cashfree API
    \Illuminate\Support\Facades\Http::fake([
        'sandbox.cashfree.com/pg/orders' => \Illuminate\Support\Facades\Http::response([
            'cf_order_id' => 'cf_test_order_123',
            'order_id' => '*',
            'payment_session_id' => 'test_session_123',
            'order_status' => 'ACTIVE',
        ], 200),
    ]);

    $response = $this->actingAs($user)->postJson('/api/wallet/topup', [
        'amount' => 500,
        'payment_method' => 'cashfree',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'transaction_id',
                'checkout_url',
                'amount',
                'amount_formatted',
                'payment_method',
                'expires_at',
            ],
        ]);

    // Verify transaction created
    expect(Transaction::count())->toBe(1);

    $transaction = Transaction::first();
    expect($transaction->amount)->toBe(50000); // 500 * 100
    expect($transaction->purpose)->toBe('Wallet TopUp');
    expect($transaction->status->value)->toBe('pending');
    expect($transaction->wallet_id)->toBe($wallet->id);
    expect($transaction->provider_gen_session)->toBe('test_session_123');
});

test('checkout page returns transaction data correctly', function () {
    // Create Cashfree integration for testing
    \App\Models\Integration::factory()->cashfree()->create();
    app(\App\Services\IntegrationServices\Payment\PaymentService::class)->refreshProviders();

    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create();

    // Mock Cashfree API
    \Illuminate\Support\Facades\Http::fake([
        'sandbox.cashfree.com/pg/orders' => \Illuminate\Support\Facades\Http::response([
            'cf_order_id' => 'cf_test_order_123',
            'order_id' => '*',
            'payment_session_id' => 'test_session_123',
            'order_status' => 'ACTIVE',
        ], 200),
    ]);

    // Create topup
    $response = $this->actingAs($user)->postJson('/api/wallet/topup', [
        'amount' => 100,
    ]);

    $transactionId = $response->json('data.transaction_id');

    // Fetch checkout data (no auth required)
    $checkoutResponse = $this->getJson('/api/checkout/'.$transactionId);

    $checkoutResponse->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'transaction' => [
                    'uuid',
                    'amount',
                    'amount_formatted',
                    'purpose',
                    'status',
                    'expires_at',
                ],
                'payment' => [
                    'provider',
                    'payment_session_id',
                    'is_sandbox',
                ],
                'redirect' => [
                    'success_url',
                    'failure_url',
                ],
            ],
        ]);
});

test('checkout status endpoint works for polling', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create();

    // Create transaction
    $transaction = Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'type' => \App\Casts\TransactionTypeCast::CREDIT,
        'status' => \App\Casts\TransactionStatusCast::PENDING,
        'amount' => 10000,
        'purpose' => 'Wallet TopUp',
    ]);

    // Check status
    $response = $this->getJson("/api/checkout/{$transaction->uuid}/status");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'status' => 'pending',
                'is_verified' => false,
                'is_expired' => false,
            ],
        ]);
});

test('wallet balance updates after successful payment', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create([
        'balance' => 0,
    ]);

    // Create pending transaction
    $transaction = Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'type' => \App\Casts\TransactionTypeCast::CREDIT,
        'status' => \App\Casts\TransactionStatusCast::PENDING,
        'amount' => 50000, // ₹500
        'purpose' => 'Wallet TopUp',
    ]);

    expect($wallet->fresh()->balance)->toBe(0);

    // Simulate payment completion (via webhook or verification)
    $transaction->update([
        'status' => \App\Casts\TransactionStatusCast::COMPLETED,
        'verified' => true,
        'verified_at' => now(),
    ]);

    // Fire event (would happen in real webhook/verification)
    event(new \App\Events\PaymentCompleted($transaction));

    // Wallet balance should be updated
    expect($wallet->fresh()->balance)->toBe(50000);
});

test('expired transactions cannot be paid', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create();

    // Create expired transaction
    $transaction = Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'type' => \App\Casts\TransactionTypeCast::CREDIT,
        'status' => \App\Casts\TransactionStatusCast::PENDING,
        'amount' => 10000,
        'purpose' => 'Wallet TopUp',
        'expires_at' => now()->subHour(),
    ]);

    $response = $this->getJson("/api/checkout/{$transaction->uuid}/status");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'is_expired' => true,
            ],
        ]);
});

test('completed transactions show error message', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create();

    $transaction = Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'type' => \App\Casts\TransactionTypeCast::CREDIT,
        'status' => \App\Casts\TransactionStatusCast::COMPLETED,
        'amount' => 10000,
        'purpose' => 'Wallet TopUp',
        'verified' => true,
        'verified_at' => now(),
    ]);

    $response = $this->getJson('/api/checkout/'.$transaction->uuid);

    // Already verified/completed transactions should return error
    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'This transaction has already been completed',
        ]);
});
