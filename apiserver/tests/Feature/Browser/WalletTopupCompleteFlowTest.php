<?php

declare(strict_types=1);

use App\Casts\TransactionStatusCast;
use App\Models\Integration;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('wallet topup checkout initializes a pending transaction', function () {
    Http::fake([
        'sandbox.cashfree.com/*' => Http::response([
            'cf_order_id' => 'CF-ORDER-001',
            'order_status' => 'ACTIVE',
            'payment_session_id' => 'SESSION-123',
            'payment_link' => 'https://sandbox.cashfree.com/pay/SESSION-123',
            'order_expiry_time' => now()->addMinutes(60)->toIso8601String(),
        ], 200),
    ]);

    Integration::factory()->cashfree()->create();

    $user = User::factory()->create([
        'email' => 'test@mintreu.com',
        'password' => bcrypt('password123'),
    ]);

    $wallet = Wallet::factory()->for($user, 'walletable')->create([
        'balance' => 0,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/wallet/topup', ['amount' => 500]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.amount', 50000)
        ->assertJsonPath('data.amount_formatted', '₹500.00')
        ->assertJsonPath('data.payment_method', 'cashfree');

    $transactionUuid = $response->json('data.transaction_id');
    expect($transactionUuid)->not->toBeNull();
    $response->assertJsonPath('data.checkout_url', route('checkout', ['transaction' => $transactionUuid]));

    $this->assertDatabaseHas('transactions', [
        'uuid' => $transactionUuid,
        'wallet_id' => $wallet->id,
        'amount' => 50000,
        'purpose' => 'Wallet TopUp',
        'status' => TransactionStatusCast::PENDING->value,
    ]);
});

test('wallet topup accepts custom amounts and reports the formatted total', function () {
    Http::fake([
        'sandbox.cashfree.com/*' => Http::response([
            'cf_order_id' => 'CF-ORDER-002',
            'order_status' => 'ACTIVE',
            'payment_session_id' => 'SESSION-456',
            'payment_link' => 'https://sandbox.cashfree.com/pay/SESSION-456',
            'order_expiry_time' => now()->addMinutes(60)->toIso8601String(),
        ], 200),
    ]);

    Integration::factory()->cashfree()->create();

    $user = User::factory()->create([
        'email' => 'test2@mintreu.com',
        'password' => bcrypt('password123'),
    ]);

    Wallet::factory()->for($user, 'walletable')->create();

    $customAmount = 750;

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/wallet/topup', ['amount' => $customAmount]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.amount', $customAmount * 100)
        ->assertJsonPath('data.amount_formatted', '₹750.00')
        ->assertJsonPath('data.checkout_url', fn (string $url) => str_contains($url, '/checkout/'));

    $transactionUuid = $response->json('data.transaction_id');
    expect($transactionUuid)->not->toBeNull();

    $this->assertDatabaseHas('transactions', [
        'uuid' => $transactionUuid,
        'amount' => $customAmount * 100,
        'purpose' => 'Wallet TopUp',
        'status' => TransactionStatusCast::PENDING->value,
    ]);
});
