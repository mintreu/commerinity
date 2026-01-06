<?php

declare(strict_types=1);

use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\IntegrationServices\Payment\DTOs\PaymentInitiateRequest;
use App\Services\IntegrationServices\Payment\DTOs\PaymentVerifyRequest;
use App\Services\IntegrationServices\Payment\Providers\NativePaymentProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->provider = new NativePaymentProvider;

    // Create user and wallet with balance
    $this->user = User::factory()->create();
    $this->wallet = Wallet::factory()->forUser($this->user)->withBalance(100000)->create(); // Rs 1000
});

describe('NativePaymentProvider Configuration', function () {
    it('returns correct slug', function () {
        expect($this->provider->getSlug())->toBe('native');
    });

    it('returns correct name', function () {
        expect($this->provider->getName())->toBe('Native Payments');
    });

    it('is always available', function () {
        expect($this->provider->isAvailable())->toBeTrue();
    });

    it('returns supported payment methods', function () {
        $methods = $this->provider->getSupportedMethods();

        expect($methods)->toContain('wallet')
            ->toContain('cash')
            ->toContain('cod')
            ->toContain('bank_transfer');
    });
});

describe('NativePaymentProvider Wallet Payment', function () {
    it('processes wallet payment successfully', function () {
        $request = new PaymentInitiateRequest(
            amountInPaisa: 50000, // Rs 500
            currency: 'INR',
            method: PaymentMethodCast::WALLET,
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: 'TXN-WALLET-001',
            purpose: 'purchase',
            description: 'Test purchase',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('completed')
            ->and($response->transactionId)->not->toBeNull();

        // Verify wallet balance was deducted
        $this->wallet->refresh();
        expect($this->wallet->balance)->toBe(50000); // Rs 500 remaining

        // Verify transaction was created
        $transaction = Transaction::where('uuid', $response->transactionId)->first();
        expect($transaction)->not->toBeNull()
            ->and($transaction->status->value)->toBe('completed')
            ->and($transaction->amount)->toBe(50000)
            ->and($transaction->payment_method->value)->toBe('wallet');
    });

    it('fails wallet payment with insufficient balance', function () {
        $request = new PaymentInitiateRequest(
            amountInPaisa: 200000, // Rs 2000 (more than balance)
            currency: 'INR',
            method: PaymentMethodCast::WALLET,
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: 'TXN-WALLET-002',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Insufficient wallet balance');

        // Verify wallet balance unchanged
        $this->wallet->refresh();
        expect($this->wallet->balance)->toBe(100000);
    });

    it('fails wallet payment with invalid wallet', function () {
        $request = new PaymentInitiateRequest(
            amountInPaisa: 10000,
            currency: 'INR',
            method: PaymentMethodCast::WALLET,
            userId: $this->user->id,
            walletId: 99999, // Non-existent wallet
            transactionId: 'TXN-WALLET-003',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Wallet not found');
    });

    it('fails wallet payment with suspended wallet', function () {
        $this->wallet->suspend();

        $request = new PaymentInitiateRequest(
            amountInPaisa: 10000,
            currency: 'INR',
            method: PaymentMethodCast::WALLET,
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: 'TXN-WALLET-004',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Wallet is not active');
    });
});

describe('NativePaymentProvider Cash Payment', function () {
    it('creates pending cash payment', function () {
        $request = new PaymentInitiateRequest(
            amountInPaisa: 50000,
            currency: 'INR',
            method: PaymentMethodCast::CASH,
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: 'TXN-CASH-001',
            purpose: 'purchase',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('pending')
            ->and($response->message)->toContain('Awaiting confirmation');

        // Verify transaction created with pending status
        $transaction = Transaction::where('uuid', $response->transactionId)->first();
        expect($transaction)->not->toBeNull()
            ->and($transaction->status->value)->toBe('pending')
            ->and($transaction->payment_method->value)->toBe('cash');
    });
});

describe('NativePaymentProvider COD Payment', function () {
    it('creates pending COD payment', function () {
        $request = new PaymentInitiateRequest(
            amountInPaisa: 75000,
            currency: 'INR',
            method: PaymentMethodCast::COD,
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: 'TXN-COD-001',
            purpose: 'order',
            expiresInMinutes: 7 * 24 * 60, // 7 days
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('pending');

        // Verify transaction
        $transaction = Transaction::where('uuid', $response->transactionId)->first();
        expect($transaction)->not->toBeNull()
            ->and($transaction->status->value)->toBe('pending')
            ->and($transaction->payment_method->value)->toBe('cod')
            ->and($transaction->expires_at)->not->toBeNull();
    });
});

describe('NativePaymentProvider Bank Transfer', function () {
    it('creates pending bank transfer payment', function () {
        $request = new PaymentInitiateRequest(
            amountInPaisa: 100000,
            currency: 'INR',
            method: PaymentMethodCast::BANK_TRANSFER,
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: 'TXN-BANK-001',
            purpose: 'subscription',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('pending');

        // Verify transaction
        $transaction = Transaction::where('uuid', $response->transactionId)->first();
        expect($transaction)->not->toBeNull()
            ->and($transaction->status->value)->toBe('pending')
            ->and($transaction->payment_method->value)->toBe('bank_transfer');
    });
});

describe('NativePaymentProvider Payment Verification', function () {
    it('verifies completed transaction', function () {
        // Create a completed transaction
        $transaction = Transaction::create([
            'uuid' => 'TXN-VERIFY-001',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 50000,
            'payment_method' => PaymentMethodCast::WALLET,
        ]);

        $request = new PaymentVerifyRequest(orderId: 'TXN-VERIFY-001');

        $response = $this->provider->verify($request);

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('completed');
    });

    it('verifies pending transaction', function () {
        $transaction = Transaction::create([
            'uuid' => 'TXN-VERIFY-002',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'status' => TransactionStatusCast::PENDING,
            'amount' => 50000,
            'payment_method' => PaymentMethodCast::COD,
        ]);

        $request = new PaymentVerifyRequest(orderId: 'TXN-VERIFY-002');

        $response = $this->provider->verify($request);

        expect($response->success)->toBeFalse()
            ->and($response->status)->toBe('pending');
    });

    it('fails verification for non-existent transaction', function () {
        $request = new PaymentVerifyRequest(orderId: 'TXN-NONEXISTENT');

        $response = $this->provider->verify($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Transaction not found');
    });
});

describe('NativePaymentProvider Confirm Payment', function () {
    it('confirms pending payment', function () {
        // Create pending transaction
        $transaction = Transaction::create([
            'uuid' => 'TXN-CONFIRM-001',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'status' => TransactionStatusCast::PENDING,
            'amount' => 25000,
            'payment_method' => PaymentMethodCast::CASH,
        ]);

        $initialBalance = $this->wallet->balance;

        $response = $this->provider->confirmPayment('TXN-CONFIRM-001');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('completed')
            ->and($response->message)->toBe('Payment confirmed');

        // Verify transaction updated
        $transaction->refresh();
        expect($transaction->status->value)->toBe('completed')
            ->and($transaction->is_verified)->toBeTrue()
            ->and($transaction->verified_at)->not->toBeNull();

        // Verify wallet debited
        $this->wallet->refresh();
        expect($this->wallet->balance)->toBe($initialBalance - 25000);
    });

    it('fails to confirm non-pending payment', function () {
        $transaction = Transaction::create([
            'uuid' => 'TXN-CONFIRM-002',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 25000,
            'payment_method' => PaymentMethodCast::CASH,
        ]);

        $response = $this->provider->confirmPayment('TXN-CONFIRM-002');

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Transaction is not pending');
    });

    it('fails to confirm non-existent payment', function () {
        $response = $this->provider->confirmPayment('TXN-NONEXISTENT');

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Transaction not found');
    });
});

describe('NativePaymentProvider Refund', function () {
    it('refunds completed wallet payment', function () {
        // First create a completed payment
        $originalBalance = $this->wallet->balance;

        $transaction = Transaction::create([
            'uuid' => 'TXN-REFUND-001',
            'wallet_id' => $this->wallet->id,
            'type' => 'debit',
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 30000,
            'payment_method' => PaymentMethodCast::WALLET,
            'currency' => 'INR',
        ]);

        // Simulate the debit that happened
        $this->wallet->decrement('balance', 30000);
        $this->wallet->increment('total_debited', 30000);

        $response = $this->provider->refund('TXN-REFUND-001', 30000, 'Customer request');

        expect($response->success)->toBeTrue()
            ->and($response->status)->toBe('refunded')
            ->and($response->message)->toBe('Refund processed successfully');

        // Verify wallet credited back
        $this->wallet->refresh();
        expect($this->wallet->balance)->toBe($originalBalance);

        // Verify original transaction marked as refunded
        $transaction->refresh();
        expect($transaction->status->value)->toBe('refunded');

        // Verify refund transaction created
        $refundTransaction = Transaction::where('parent_transaction_id', $transaction->id)->first();
        expect($refundTransaction)->not->toBeNull()
            ->and($refundTransaction->type->value)->toBe('refund')
            ->and($refundTransaction->status->value)->toBe('completed')
            ->and($refundTransaction->amount)->toBe(30000);
    });

    it('fails refund for non-existent transaction', function () {
        $response = $this->provider->refund('TXN-NONEXISTENT', 10000);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toBe('Transaction not found');
    });
});

describe('NativePaymentProvider Unsupported Method', function () {
    it('rejects unsupported payment method', function () {
        $request = new PaymentInitiateRequest(
            amountInPaisa: 50000,
            currency: 'INR',
            method: PaymentMethodCast::RAZORPAY, // Not native
            userId: $this->user->id,
            walletId: $this->wallet->id,
            transactionId: 'TXN-UNSUPPORTED',
        );

        $response = $this->provider->initiate($request);

        expect($response->success)->toBeFalse()
            ->and($response->message)->toContain('Unsupported payment method');
    });
});
