<?php

declare(strict_types=1);

use App\Casts\BeneficiaryStatusCast;
use App\Models\BeneficiaryAccount;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');

    // Ensure wallet exists
    $this->wallet = Wallet::firstOrCreate([
        'walletable_type' => User::class,
        'walletable_id' => $this->user->id,
    ], [
        'balance' => 100000, // ₹1000
        'currency' => 'INR',
    ]);
});

// ========================================
// Index (List Beneficiaries)
// ========================================

it('returns empty list when no beneficiaries exist', function () {
    $response = $this->getJson('/api/wallet/beneficiaries');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

it('returns list of beneficiaries for authenticated user', function () {
    // Create some beneficiaries
    BeneficiaryAccount::factory()->count(3)->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
    ]);

    $response = $this->getJson('/api/wallet/beneficiaries');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('orders beneficiaries with default first', function () {
    $nonDefault = BeneficiaryAccount::factory()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
        'is_default' => false,
    ]);

    $default = BeneficiaryAccount::factory()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
        'is_default' => true,
    ]);

    $response = $this->getJson('/api/wallet/beneficiaries');

    $response->assertSuccessful();
    expect($response->json('data.0.uuid'))->toBe($default->uuid);
});

// ========================================
// Store (Add Beneficiary)
// ========================================

it('can add a bank account beneficiary', function () {
    $response = $this->postJson('/api/wallet/beneficiaries', [
        'type' => 'savings',
        'holder_name' => 'John Doe',
        'account_number' => '123456789012',
        'confirm_account_number' => '123456789012',
        'ifsc_code' => 'HDFC0001234',
        'bank_name' => 'HDFC Bank',
        'branch_name' => 'Main Branch',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Bank account added successfully. Pending verification.',
        ]);

    $this->assertDatabaseHas('beneficiary_accounts', [
        'wallet_id' => $this->wallet->id,
        'type' => 'savings',
        'holder_name' => 'John Doe',
        'ifsc_code' => 'HDFC0001234',
        'status' => 'pending',
    ]);
});

it('can add a UPI beneficiary', function () {
    $response = $this->postJson('/api/wallet/beneficiaries', [
        'type' => 'upi',
        'holder_name' => 'Jane Doe',
        'upi_id' => 'jane@upi',
    ]);

    $response->assertStatus(201)
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('beneficiary_accounts', [
        'wallet_id' => $this->wallet->id,
        'type' => 'upi',
        'holder_name' => 'Jane Doe',
        'upi_id' => 'jane@upi',
    ]);
});

it('makes first beneficiary default automatically', function () {
    $response = $this->postJson('/api/wallet/beneficiaries', [
        'type' => 'savings',
        'holder_name' => 'John Doe',
        'account_number' => '123456789012',
        'confirm_account_number' => '123456789012',
        'ifsc_code' => 'HDFC0001234',
    ]);

    $response->assertStatus(201);
    expect($response->json('data.beneficiary.is_default'))->toBeTrue();
});

it('validates required fields for bank account', function () {
    $response = $this->postJson('/api/wallet/beneficiaries', [
        'type' => 'savings',
        'holder_name' => 'John Doe',
        // Missing account_number and ifsc_code
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['account_number', 'ifsc_code']);
});

it('validates IFSC code format', function () {
    $response = $this->postJson('/api/wallet/beneficiaries', [
        'type' => 'savings',
        'holder_name' => 'John Doe',
        'account_number' => '123456789012',
        'confirm_account_number' => '123456789012',
        'ifsc_code' => 'INVALID',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['ifsc_code']);
});

it('validates UPI ID format', function () {
    $response = $this->postJson('/api/wallet/beneficiaries', [
        'type' => 'upi',
        'holder_name' => 'Jane Doe',
        'upi_id' => 'invalid-upi-id',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['upi_id']);
});

it('validates account number confirmation matches', function () {
    $response = $this->postJson('/api/wallet/beneficiaries', [
        'type' => 'savings',
        'holder_name' => 'John Doe',
        'account_number' => '123456789012',
        'confirm_account_number' => '999999999999',
        'ifsc_code' => 'HDFC0001234',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['confirm_account_number']);
});

// ========================================
// Show (Get Single Beneficiary)
// ========================================

it('can get a single beneficiary by uuid', function () {
    $beneficiary = BeneficiaryAccount::factory()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
    ]);

    $response = $this->getJson("/api/wallet/beneficiaries/{$beneficiary->uuid}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'beneficiary' => [
                    'uuid' => $beneficiary->uuid,
                ],
            ],
        ]);
});

it('returns 404 for non-existent beneficiary', function () {
    $response = $this->getJson('/api/wallet/beneficiaries/INVALID123');

    $response->assertNotFound();
});

// ========================================
// Destroy (Delete Beneficiary)
// ========================================

it('can delete a beneficiary', function () {
    $beneficiary = BeneficiaryAccount::factory()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
    ]);

    $response = $this->deleteJson("/api/wallet/beneficiaries/{$beneficiary->uuid}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Bank account removed successfully',
        ]);

    $this->assertSoftDeleted('beneficiary_accounts', ['id' => $beneficiary->id]);
});

it('makes another beneficiary default when default is deleted', function () {
    $default = BeneficiaryAccount::factory()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
        'is_default' => true,
    ]);

    $other = BeneficiaryAccount::factory()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
        'is_default' => false,
    ]);

    $this->deleteJson("/api/wallet/beneficiaries/{$default->uuid}");

    $other->refresh();
    expect($other->is_default)->toBeTrue();
});

// ========================================
// Set Default
// ========================================

it('can set a beneficiary as default', function () {
    $first = BeneficiaryAccount::factory()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
        'is_default' => true,
    ]);

    $second = BeneficiaryAccount::factory()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
        'is_default' => false,
    ]);

    $response = $this->postJson("/api/wallet/beneficiaries/{$second->uuid}/default");

    $response->assertSuccessful();

    $first->refresh();
    $second->refresh();

    expect($first->is_default)->toBeFalse();
    expect($second->is_default)->toBeTrue();
});

// ========================================
// Account Types
// ========================================

it('returns available account types', function () {
    $response = $this->getJson('/api/wallet/beneficiaries/types');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'types' => [
                    '*' => ['value', 'label', 'is_bank', 'is_upi'],
                ],
            ],
        ]);

    expect($response->json('data.types'))->toHaveCount(3);
});

// ========================================
// Verify Beneficiary (Demo Mode)
// ========================================

it('can verify a beneficiary in non-production environment', function () {
    $beneficiary = BeneficiaryAccount::factory()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
        'status' => BeneficiaryStatusCast::PENDING,
    ]);

    $response = $this->postJson("/api/wallet/beneficiaries/{$beneficiary->uuid}/verify");

    $response->assertSuccessful()
        ->assertJson(['success' => true]);

    $beneficiary->refresh();
    expect($beneficiary->status)->toBe(BeneficiaryStatusCast::VERIFIED);
    expect($beneficiary->verified_at)->not->toBeNull();
});

// ========================================
// Authentication
// ========================================

it('requires authentication for all endpoints', function () {
    // Logout
    auth()->forgetGuards();

    $this->getJson('/api/wallet/beneficiaries')->assertUnauthorized();
    $this->postJson('/api/wallet/beneficiaries', [])->assertUnauthorized();
    $this->getJson('/api/wallet/beneficiaries/ABC123')->assertUnauthorized();
    $this->deleteJson('/api/wallet/beneficiaries/ABC123')->assertUnauthorized();
    $this->postJson('/api/wallet/beneficiaries/ABC123/default')->assertUnauthorized();
});

// ========================================
// User Isolation
// ========================================

it('cannot access another users beneficiaries', function () {
    $otherUser = User::factory()->create();
    $otherWallet = Wallet::create([
        'walletable_type' => User::class,
        'walletable_id' => $otherUser->id,
        'balance' => 0,
        'currency' => 'INR',
    ]);

    $otherBeneficiary = BeneficiaryAccount::factory()->create([
        'wallet_id' => $otherWallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $otherUser->id,
    ]);

    // Try to access other user's beneficiary
    $response = $this->getJson("/api/wallet/beneficiaries/{$otherBeneficiary->uuid}");
    $response->assertNotFound();

    // Try to delete other user's beneficiary
    $response = $this->deleteJson("/api/wallet/beneficiaries/{$otherBeneficiary->uuid}");
    $response->assertNotFound();
});
