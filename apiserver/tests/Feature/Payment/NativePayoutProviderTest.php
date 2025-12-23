<?php

declare(strict_types=1);

use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Models\BeneficiaryAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Payment\DTOs\PayoutRequest;
use App\Services\Payment\Providers\NativePayoutProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->provider = new NativePayoutProvider;

    // Create user and wallet with balance
    $this->user = User::factory()->create();
    $this->wallet = Wallet::factory()->forUser($this->user)->withBalance(500000)->create(); // Rs 5000

    // Create verified beneficiary account
    $this->beneficiary = BeneficiaryAccount::create([
        'accountable_type' => User::class,
        'accountable_id' => $this->user->id,
        'wallet_id' => $this->wallet->id,
        'type' => 'savings', // savings, current, or upi
        'holder_name' => 'Test User',
        'account_number' => '1234567890123456',
        'ifsc_code' => 'HDFC0001234',
        'bank_name' => 'HDFC Bank',
        'status' => 'verified',
        'verified_at' => now(),
    ]);
});

describe('NativePayoutProvider Configuration', function () {
    it('returns correct slug', function () {
        expect($this->provider->getSlug())->toBe('native');
    });

    it('returns correct name', function () {
        expect($this->provider->getName())->toBe('Manual Payout');
    });

    it('is always available', function () {
        expect($this->provider->isAvailable())->toBeTrue();
    });

    it('returns supported payout methods', function () {
        $methods = $this->provider->getSupportedMethods();

        expect($methods)->toContain('payout_bank')
            ->toContain('payout_upi');
    });
});

describe('NativePayoutProvider Initiate', function () {
    it('initiates payout successfully', function () {
        $request = new PayoutRequest(
            amountInPaisa: 100000, // Rs 1000
            currency: 'INR',
            method: PaymentMethodCast::PAYOUT_BANK,
            beneficiaryAccountId: $this->beneficiary->id,
            walletId: $this->wallet->id,
            userId: $this->user->id,
            transactionId: 'TXN-PAYOUT-001',
            purpose: 'withdrawal',
        );

        $initialBalance = $this->wallet->balance;

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('pending')
            ->and($response->message)->toContain('Processing within');

        // Verify wallet balance moved to hold
        $this->wallet->refresh();
        expect($this->wallet->balance)->toBe($initialBalance - 100000)
            ->and($this->wallet->hold_balance)->toBe(100000);

        // Verify transaction created
        $transaction = Transaction::where('uuid', $response->transactionId)->first();
        expect($transaction)->not->toBeNull()
            ->and($transaction->status->value)->toBe('pending')
            ->and($transaction->amount)->toBe(100000)
            ->and($transaction->metadata['beneficiary_id'])->toBe($this->beneficiary->id);
    });

    it('fails payout with insufficient balance', function () {
        $request = new PayoutRequest(
            amountInPaisa: 1000000, // Rs 10000 (more than balance)
            currency: 'INR',
            method: PaymentMethodCast::PAYOUT_BANK,
            beneficiaryAccountId: $this->beneficiary->id,
            walletId: $this->wallet->id,
            userId: $this->user->id,
            transactionId: 'TXN-PAYOUT-002',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Insufficient wallet balance');
    });

    it('fails payout with invalid wallet', function () {
        $request = new PayoutRequest(
            amountInPaisa: 100000,
            currency: 'INR',
            method: PaymentMethodCast::PAYOUT_BANK,
            beneficiaryAccountId: $this->beneficiary->id,
            walletId: 99999,
            userId: $this->user->id,
            transactionId: 'TXN-PAYOUT-003',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Wallet not found');
    });

    it('fails payout with suspended wallet', function () {
        $this->wallet->suspend();

        $request = new PayoutRequest(
            amountInPaisa: 100000,
            currency: 'INR',
            method: PaymentMethodCast::PAYOUT_BANK,
            beneficiaryAccountId: $this->beneficiary->id,
            walletId: $this->wallet->id,
            userId: $this->user->id,
            transactionId: 'TXN-PAYOUT-004',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Wallet is not active');
    });

    it('fails payout with invalid beneficiary', function () {
        $request = new PayoutRequest(
            amountInPaisa: 100000,
            currency: 'INR',
            method: PaymentMethodCast::PAYOUT_BANK,
            beneficiaryAccountId: 99999,
            walletId: $this->wallet->id,
            userId: $this->user->id,
            transactionId: 'TXN-PAYOUT-005',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Beneficiary account not found');
    });

    it('fails payout with unverified beneficiary', function () {
        $this->beneficiary->update(['status' => 'pending']);

        $request = new PayoutRequest(
            amountInPaisa: 100000,
            currency: 'INR',
            method: PaymentMethodCast::PAYOUT_BANK,
            beneficiaryAccountId: $this->beneficiary->id,
            walletId: $this->wallet->id,
            userId: $this->user->id,
            transactionId: 'TXN-PAYOUT-006',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Beneficiary account is not verified');
    });
});

describe('NativePayoutProvider Check Status', function () {
    it('returns status for completed payout', function () {
        $transaction = Transaction::create([
            'uuid' => 'TXN-STATUS-001',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
            'payment_method' => PaymentMethodCast::PAYOUT_BANK,
            'metadata' => ['utr_number' => 'UTR123456789'],
        ]);

        $response = $this->provider->checkStatus('TXN-STATUS-001');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('completed')
            ->and($response->utrNumber)->toBe('UTR123456789');
    });

    it('returns status for pending payout', function () {
        $transaction = Transaction::create([
            'uuid' => 'TXN-STATUS-002',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'status' => TransactionStatusCast::PENDING,
            'amount' => 100000,
            'payment_method' => PaymentMethodCast::PAYOUT_BANK,
        ]);

        $response = $this->provider->checkStatus('TXN-STATUS-002');

        expect($response->success)->toBeFalse()
            ->and($response->status)->toBe('pending');
    });

    it('fails for non-existent payout', function () {
        $response = $this->provider->checkStatus('TXN-NONEXISTENT');

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Payout not found');
    });
});

describe('NativePayoutProvider Confirm Payout', function () {
    it('confirms pending payout with UTR', function () {
        // Create pending payout
        $transaction = Transaction::create([
            'uuid' => 'TXN-CONFIRM-001',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'status' => TransactionStatusCast::PENDING,
            'amount' => 100000,
            'payment_method' => PaymentMethodCast::PAYOUT_BANK,
        ]);

        // Simulate hold balance
        $this->wallet->update(['hold_balance' => 100000]);

        $response = $this->provider->confirmPayout('TXN-CONFIRM-001', 'UTR987654321');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('completed')
            ->and($response->utrNumber)->toBe('UTR987654321');

        // Verify transaction updated
        $transaction->refresh();
        expect($transaction->status->value)->toBe('completed')
            ->and($transaction->is_verified)->toBeTrue()
            ->and($transaction->metadata['utr_number'])->toBe('UTR987654321');

        // Verify hold released
        $this->wallet->refresh();
        expect($this->wallet->hold_balance)->toBe(0);
    });

    it('confirms pending payout without UTR', function () {
        $transaction = Transaction::create([
            'uuid' => 'TXN-CONFIRM-002',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'status' => TransactionStatusCast::PENDING,
            'amount' => 50000,
            'payment_method' => PaymentMethodCast::PAYOUT_BANK,
        ]);

        $this->wallet->update(['hold_balance' => 50000]);

        $response = $this->provider->confirmPayout('TXN-CONFIRM-002');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('completed');
    });

    it('fails to confirm non-pending payout', function () {
        $transaction = Transaction::create([
            'uuid' => 'TXN-CONFIRM-003',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
            'payment_method' => PaymentMethodCast::PAYOUT_BANK,
        ]);

        $response = $this->provider->confirmPayout('TXN-CONFIRM-003');

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Payout is not pending');
    });

    it('fails to confirm non-existent payout', function () {
        $response = $this->provider->confirmPayout('TXN-NONEXISTENT');

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Payout not found');
    });
});

describe('NativePayoutProvider Cancel Payout', function () {
    it('cancels pending payout', function () {
        $initialBalance = $this->wallet->balance;

        // Create pending payout and hold
        $transaction = Transaction::create([
            'uuid' => 'TXN-CANCEL-001',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'status' => TransactionStatusCast::PENDING,
            'amount' => 100000,
            'payment_method' => PaymentMethodCast::PAYOUT_BANK,
        ]);

        // Simulate the hold that happened during initiation
        $this->wallet->decrement('balance', 100000);
        $this->wallet->increment('hold_balance', 100000);

        $response = $this->provider->cancelPayout('TXN-CANCEL-001', 'User requested cancellation');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('cancelled')
            ->and($response->message)->toContain('Amount returned to wallet');

        // Verify transaction cancelled
        $transaction->refresh();
        expect($transaction->status->value)->toBe('cancelled')
            ->and($transaction->notes)->toBe('User requested cancellation');

        // Verify balance restored
        $this->wallet->refresh();
        expect($this->wallet->balance)->toBe($initialBalance)
            ->and($this->wallet->hold_balance)->toBe(0);
    });

    it('fails to cancel non-pending payout', function () {
        $transaction = Transaction::create([
            'uuid' => 'TXN-CANCEL-002',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
            'payment_method' => PaymentMethodCast::PAYOUT_BANK,
        ]);

        $response = $this->provider->cancelPayout('TXN-CANCEL-002');

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Payout is not pending');
    });

    it('fails to cancel non-existent payout', function () {
        $response = $this->provider->cancelPayout('TXN-NONEXISTENT');

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Payout not found');
    });
});

describe('NativePayoutProvider UPI Payout', function () {
    it('initiates UPI payout successfully', function () {
        // Create UPI beneficiary
        $upiBeneficiary = BeneficiaryAccount::create([
            'accountable_type' => User::class,
            'accountable_id' => $this->user->id,
            'wallet_id' => $this->wallet->id,
            'type' => 'upi', // Valid enum value
            'holder_name' => 'Test User',
            'upi_id' => 'test@upi',
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        $request = new PayoutRequest(
            amountInPaisa: 50000,
            currency: 'INR',
            method: PaymentMethodCast::PAYOUT_UPI,
            beneficiaryAccountId: $upiBeneficiary->id,
            walletId: $this->wallet->id,
            userId: $this->user->id,
            transactionId: 'TXN-UPI-001',
            purpose: 'withdrawal',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('pending');

        // Verify transaction metadata - type is now the BeneficiaryTypeCast enum value
        $transaction = Transaction::where('uuid', $response->transactionId)->first();
        expect($transaction->metadata['beneficiary_type']->value ?? $transaction->metadata['beneficiary_type'])->toBe('upi');
    });
});
