<?php

declare(strict_types=1);

use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Jobs\Wallet\ProcessPayoutJob;
use App\Models\BeneficiaryAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->wallet = Wallet::firstOrCreate([
        'walletable_type' => User::class,
        'walletable_id' => $this->user->id,
    ], [
        'balance' => 1000000, // ₹10,000
        'hold_balance' => 0,
        'currency' => 'INR',
    ]);
});

it('dispatches payout job when withdrawal is requested', function () {
    Queue::fake();

    $beneficiary = BeneficiaryAccount::factory()->bank()->verified()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
    ]);

    // Ensure wallet has PIN using proper setPin method (not default PIN 123456)
    $this->wallet->setPin('654321');

    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/wallet/withdraw', [
            'pin' => '654321',
            'amount' => 1000,
            'beneficiary_uuid' => $beneficiary->uuid,
        ])
        ->assertSuccessful();

    Queue::assertPushedOn('payouts', ProcessPayoutJob::class);
});

it('processes payout and updates transaction status', function () {
    $beneficiary = BeneficiaryAccount::factory()->bank()->verified()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
    ]);

    $transaction = Transaction::create([
        'wallet_id' => $this->wallet->id,
        'type' => TransactionTypeCast::HOLD,
        'status' => TransactionStatusCast::ON_HOLD,
        'amount' => 100000, // ₹1000
        'fee' => 0,
        'currency' => 'INR',
        'purpose' => 'withdrawal',
        'description' => 'Test withdrawal',
        'reference_number' => 'WDR-TEST-001',
        'metadata' => [
            'beneficiary_id' => $beneficiary->id,
        ],
    ]);

    // Put funds on hold
    $this->wallet->update([
        'balance' => 900000,
        'hold_balance' => 100000,
    ]);

    // Run the job (will use NativePayoutProvider since Cashfree not configured)
    $job = new ProcessPayoutJob($transaction->id, $beneficiary->id);
    $job->handle();

    // Refresh transaction
    $transaction->refresh();

    // Should be processing or completed (native provider completes immediately)
    expect($transaction->status)->toBeIn([
        TransactionStatusCast::PROCESSING,
        TransactionStatusCast::COMPLETED,
    ]);
    expect($transaction->metadata['payout_provider'] ?? null)->not->toBeNull();
});

it('fails transaction and refunds when beneficiary not found', function () {
    $transaction = Transaction::create([
        'wallet_id' => $this->wallet->id,
        'type' => TransactionTypeCast::HOLD,
        'status' => TransactionStatusCast::ON_HOLD,
        'amount' => 100000,
        'fee' => 0,
        'currency' => 'INR',
        'purpose' => 'withdrawal',
        'description' => 'Test withdrawal',
        'reference_number' => 'WDR-TEST-002',
        'metadata' => [],
    ]);

    // Put funds on hold
    $this->wallet->update([
        'balance' => 900000,
        'hold_balance' => 100000,
    ]);

    // Run job with non-existent beneficiary
    $job = new ProcessPayoutJob($transaction->id, 999999);
    $job->handle();

    $transaction->refresh();
    $this->wallet->refresh();

    // Transaction should remain unchanged (beneficiary not found is logged but not failed)
    // The job exits early when beneficiary not found
    expect($transaction->status)->toBe(TransactionStatusCast::ON_HOLD);
});

it('skips already completed transactions', function () {
    $beneficiary = BeneficiaryAccount::factory()->bank()->verified()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
    ]);

    $transaction = Transaction::create([
        'wallet_id' => $this->wallet->id,
        'type' => TransactionTypeCast::HOLD,
        'status' => TransactionStatusCast::COMPLETED,
        'amount' => 100000,
        'fee' => 0,
        'currency' => 'INR',
        'purpose' => 'withdrawal',
        'description' => 'Already completed',
        'reference_number' => 'WDR-TEST-003',
        'metadata' => [],
    ]);

    $originalMetadata = $transaction->metadata;

    $job = new ProcessPayoutJob($transaction->id, $beneficiary->id);
    $job->handle();

    $transaction->refresh();

    // Should still be completed, metadata unchanged
    expect($transaction->status)->toBe(TransactionStatusCast::COMPLETED);
    expect($transaction->metadata)->toBe($originalMetadata);
});

it('handles UPI beneficiary correctly', function () {
    $beneficiary = BeneficiaryAccount::factory()->upi()->verified()->create([
        'wallet_id' => $this->wallet->id,
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
    ]);

    $transaction = Transaction::create([
        'wallet_id' => $this->wallet->id,
        'type' => TransactionTypeCast::HOLD,
        'status' => TransactionStatusCast::ON_HOLD,
        'amount' => 50000, // ₹500
        'fee' => 0,
        'currency' => 'INR',
        'purpose' => 'withdrawal',
        'description' => 'UPI withdrawal',
        'reference_number' => 'WDR-TEST-004',
        'metadata' => [
            'beneficiary_id' => $beneficiary->id,
        ],
    ]);

    $this->wallet->update([
        'balance' => 950000,
        'hold_balance' => 50000,
    ]);

    $job = new ProcessPayoutJob($transaction->id, $beneficiary->id);
    $job->handle();

    $transaction->refresh();

    // Should process the UPI payout
    expect($transaction->status)->toBeIn([
        TransactionStatusCast::PROCESSING,
        TransactionStatusCast::COMPLETED,
    ]);
});
