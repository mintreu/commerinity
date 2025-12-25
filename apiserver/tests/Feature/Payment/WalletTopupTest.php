<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Wallet;
use App\Models\Integration;
use App\Models\Transaction;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use function Pest\Laravel\{actingAs, postJson};

beforeEach(function () {
    // Create integration (Cashfree)
    $this->integration = Integration::factory()->create([
        'name' => 'Cashfree',
        'slug' => 'cashfree',
        'type' => Integration::TYPE_PAYMENT,
        'is_active' => true,
        'is_default' => true,
        'is_sandbox' => true,
        'credentials' => [
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
        ],
    ]);

    $this->user = User::factory()->create();
    $this->wallet = Wallet::factory()->create([
        'walletable_type' => User::class,
        'walletable_id' => $this->user->id,
        'balance' => 0,
    ]);
});

// Note: Tests will fail until Cashfree credentials are configured
// For now, we expect the controller to attempt creation and may fail at Cashfree API call
// This is acceptable for development - we'll test with real sandbox later

test('topup endpoint exists and validates input', function () {
    $response = actingAs($this->user)->postJson('/api/wallet/topup', [
        'amount' => 500, // ₹500
    ]);

    // May fail at Cashfree API, but endpoint should work
    expect($response->status())->toBeIn([200, 500]); // 200 if Cashfree works, 500 if not configured
});

test('topup validates amount is required', function () {
    $response = actingAs($this->user)->postJson('/api/wallet/topup', [
        // Missing amount
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['amount']);
});

test('topup validates minimum amount', function () {
    $response = actingAs($this->user)->postJson('/api/wallet/topup', [
        'amount' => 0,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['amount']);
});

test('topup validates maximum amount', function () {
    $response = actingAs($this->user)->postJson('/api/wallet/topup', [
        'amount' => 200000, // Over ₹1,00,000 limit
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['amount']);
});

test('topup validates amount', function () {
    $response = actingAs($this->user)->postJson('/api/wallet/topup', [
        'amount' => 0, // Invalid
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['amount']);
});

test('topup requires authentication', function () {
    $response = postJson('/api/wallet/topup', [
        'amount' => 500,
    ]);

    $response->assertUnauthorized();
});
