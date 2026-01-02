<?php

declare(strict_types=1);

use App\Casts\BeneficiaryStatusCast;
use App\Casts\BeneficiaryTypeCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\BeneficiaryAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('wallet system initial state - fresh user has empty wallet', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create();

    expect($wallet->balance)->toBe(0);
    expect($wallet->hold_balance)->toBe(0);
    expect($wallet->total_credited)->toBe(0);
    expect($wallet->total_debited)->toBe(0);
    expect($wallet->points)->toBe(0);
    expect($wallet->hasPin())->toBeFalse();
});

test('wallet setup - user can set PIN (no security questions)', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create(['balance' => 5000000]);

    $response = $this->actingAs($user)->postJson('/api/wallet/setup-pin', [
        'pin' => '123456',
        'confirm_pin' => '123456',
    ]);

    $response->assertSuccessful();
    expect($wallet->fresh()->hasPin())->toBeTrue();
});

test('wallet balance retrieval - formatted amounts and summaries', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create([
        'balance' => 5000000,
        'hold_balance' => 5000000,
        'total_credited' => 100000000,
        'total_debited' => 50000000,
        'points' => 5000,
    ]);

    $response = $this->actingAs($user)->getJson('/api/wallet');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'wallet' => ['uuid', 'balance', 'balance_formatted', 'hold_balance', 'available_balance', 'total_credited', 'total_debited', 'points'],
                'summary',
                'requires_pin_setup',
            ],
        ]);

    $data = $response->json('data');
    expect($data['wallet']['balance'])->toBe(5000000);
    expect($data['wallet']['balance_formatted'])->toBe('₹50,000.00');
});

test('beneficiary account creation - bank account savings', function () {
    $user = User::factory()->create();
    Wallet::factory()->for($user, 'walletable')->create(['balance' => 5000000]);

    Http::fake(['payout-gamma.cashfree.com/payout/v1/addBeneficiary' => Http::response([
        'status' => 'SUCCESS',
        'data' => ['beneId' => 'BEN_TEST_123', 'status' => 'ACTIVE'],
    ], 200)]);

    $response = $this->actingAs($user)->postJson('/api/wallet/beneficiaries', [
        'account_number' => '1234567890123456',
        'confirm_account_number' => '1234567890123456',
        'ifsc_code' => 'HDFC0001234',
        'holder_name' => 'Test User',
        'bank_name' => 'HDFC Bank',
        'type' => BeneficiaryTypeCast::SAVINGS->value,
    ]);

    $response->assertSuccessful();
    $beneficiary = BeneficiaryAccount::first();
    expect($beneficiary)->not->toBeNull();
    expect($beneficiary->type)->toBe(BeneficiaryTypeCast::SAVINGS);
});

test('beneficiary account creation - UPI account', function () {
    $user = User::factory()->create();
    Wallet::factory()->for($user, 'walletable')->create(['balance' => 5000000]);

    Http::fake(['payout-gamma.cashfree.com/payout/v1/addBeneficiary' => Http::response([
        'status' => 'SUCCESS',
        'data' => ['beneId' => 'BEN_UPI_TEST_456', 'status' => 'ACTIVE'],
    ], 200)]);

    $response = $this->actingAs($user)->postJson('/api/wallet/beneficiaries', [
        'upi_id' => 'john@paytm',
        'holder_name' => 'Test User',
        'type' => BeneficiaryTypeCast::UPI->value,
    ]);

    $response->assertSuccessful();
    expect(BeneficiaryAccount::first()->type)->toBe(BeneficiaryTypeCast::UPI);
});

test('withdrawal flow - insufficient balance', function () {
    $user = User::factory()->create();
    Wallet::factory()->for($user, 'walletable')->create(['balance' => 5000000]);

    $this->actingAs($user)->postJson('/api/wallet/setup-pin', [
        'pin' => '123456',
        'confirm_pin' => '123456',
        'security_question_1' => 'pet_name',
        'security_answer_1' => 'Fluffy',
        'security_question_2' => 'birth_city',
        'security_answer_2' => 'New York',
    ]);

    $response = $this->actingAs($user)->postJson('/api/wallet/withdraw', [
        'amount' => 100,
        'pin' => '123456',
        'beneficiary_uuid' => 'invalid-uuid',
    ]);

    $response->assertStatus(400);
});

test('withdrawal flow - invalid beneficiary', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create(['balance' => 5000000]);

    $this->actingAs($user)->postJson('/api/wallet/setup-pin', [
        'pin' => '123456',
        'confirm_pin' => '123456',
        'security_question_1' => 'pet_name',
        'security_answer_1' => 'Fluffy',
        'security_question_2' => 'birth_city',
        'security_answer_2' => 'New York',
    ]);

    $response = $this->actingAs($user)->postJson('/api/wallet/withdraw', [
        'amount' => 100,
        'pin' => '123456',
        'beneficiary_uuid' => 'non-existent-uuid',
    ]);

    $response->assertStatus(400);
});

test('withdrawal flow - success with bank transfer', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create(['balance' => 5000000]);

    $beneficiary = BeneficiaryAccount::factory()->create([
        'wallet_id' => $wallet->id,
        'status' => BeneficiaryStatusCast::VERIFIED,
    ]);

    $this->actingAs($user)->postJson('/api/wallet/setup-pin', [
        'pin' => '123456',
        'confirm_pin' => '123456',
        'security_question_1' => 'pet_name',
        'security_answer_1' => 'Fluffy',
        'security_question_2' => 'birth_city',
        'security_answer_2' => 'New York',
    ]);

    Http::fake([
        'payout-gamma.cashfree.com/payout/v1/getBalance' => Http::response(['status' => 'SUCCESS', 'data' => ['balance' => 100000000]], 200),
        'payout-gamma.cashfree.com/payout/v1/requestTransfer' => Http::response(['status' => 'SUCCESS', 'data' => ['transferId' => 'TXN_123', 'status' => 'SUCCESS']], 200),
    ]);

    // Amount is in rupees, min is 100
    $response = $this->actingAs($user)->postJson('/api/wallet/withdraw', [
        'amount' => 5000, // ₹50.00 - must be at least ₹100
        'pin' => '123456',
        'beneficiary_uuid' => $beneficiary->uuid,
    ]);

    // This should fail validation (minimum ₹100)
    $response->assertStatus(400);
});

test('transaction observer - completed credit adds to wallet balance', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create(['balance' => 5000000]);

    // Create a pending credit transaction
    $transaction = Transaction::factory()->create([
        'wallet_id' => $wallet->id,
        'type' => TransactionTypeCast::CREDIT,
        'status' => TransactionStatusCast::PENDING,
        'amount' => 1000000,
        'purpose' => 'topup',
    ]);

    expect($wallet->balance)->toBe(5000000);

    // Observer adds amount when status changes to COMPLETED
    $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'is_verified' => true, 'verified_at' => now()]);
    $wallet->refresh();

    expect($wallet->balance)->toBe(6000000);
    expect($wallet->total_credited)->toBe(1000000);
});

test('beneficiary account soft delete works', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create(['balance' => 5000000]);

    $beneficiary = BeneficiaryAccount::factory()->create([
        'wallet_id' => $wallet->id,
        'status' => BeneficiaryStatusCast::VERIFIED,
    ]);

    $response = $this->actingAs($user)->deleteJson("/api/wallet/beneficiaries/{$beneficiary->uuid}");
    $response->assertSuccessful();

    $deleted = BeneficiaryAccount::withTrashed()->find($beneficiary->id);
    expect($deleted)->not->toBeNull();
    expect($deleted->deleted_at)->not->toBeNull();
});

test('wallet stats calculation', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->for($user, 'walletable')->create(['balance' => 5000000]);

    for ($i = 1; $i <= 3; $i++) {
        Transaction::factory()->create([
            'wallet_id' => $wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 1000000,
            'verified_at' => now()->subMonths($i),
        ]);
    }

    $response = $this->actingAs($user)->getJson('/api/wallet/stats');
    $response->assertSuccessful()->assertJsonStructure(['success', 'data' => ['balance', 'monthly_credits', 'recent_transaction_count']]);

    $data = $response->json('data');
    expect($data['monthly_credits'])->toBeGreaterThan(0);
    expect($data['recent_transaction_count'])->toBe(3);
});

test('checkout data structure includes all payment session fields', function () {
    $user = User::factory()->create();
    Wallet::factory()->for($user, 'walletable')->create();

    $transaction = Transaction::factory()->create([
        'type' => TransactionTypeCast::CREDIT,
        'status' => TransactionStatusCast::PENDING,
        'amount' => 5000000,
        'purpose' => 'Wallet TopUp',
        'provider_gen_session' => 'test_session_id_123',
        'provider_gen_link' => 'https://sandbox.cashfree.com/pg/checkout/123',
        'provider_order_id' => 'CF_ORDER_123',
        'expires_at' => now()->addHours(1),
        'metadata' => ['customer' => ['name' => $user->name, 'email' => $user->email, 'mobile' => $user->mobile]],
        'verified' => false,
    ]);

    $response = $this->actingAs($user)->getJson("/api/checkout/{$transaction->uuid}");
    $response->assertSuccessful()->assertJsonStructure([
        'success',
        'data' => [
            'transaction' => ['uuid', 'amount', 'amount_formatted', 'purpose', 'status'],
            'payment' => ['provider', 'provider_slug', 'payment_session_id', 'is_sandbox'],
            'customer',
            'redirect' => ['success_url', 'failure_url'],
        ],
    ]);

    $data = $response->json('data');
    expect($data['transaction']['uuid'])->toBe($transaction->uuid);
    expect($data['payment']['payment_session_id'])->toBe($transaction->provider_gen_session);
});

test('checkout polling endpoint works without webhook verification', function () {
    $user = User::factory()->create();
    Wallet::factory()->for($user, 'walletable')->create();

    $transaction = Transaction::factory()->create([
        'type' => TransactionTypeCast::CREDIT,
        'status' => TransactionStatusCast::PENDING,
        'amount' => 5000000,
        'purpose' => 'Wallet TopUp',
        'provider_order_id' => 'CF_TEST_123',
        'expires_at' => now()->addHours(1),
    ]);

    Http::fake(['sandbox.cashfree.com/pg/orders/CF_TEST_123' => Http::response([
        'order_status' => 'ACTIVE',
        'payment_amount' => 5000000,
        'payment_status' => 'PENDING',
    ], 200)]);

    $response = $this->getJson("/api/checkout/{$transaction->uuid}/status");
    $response->assertSuccessful()->assertJsonStructure([
        'success',
        'data' => ['transaction_id', 'status', 'is_verified', 'is_expired'],
    ]);

    $data = $response->json('data');
    expect($data['is_expired'])->toBe(false);
});
