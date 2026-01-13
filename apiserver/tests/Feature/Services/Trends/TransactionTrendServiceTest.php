<?php

declare(strict_types=1);

use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Trends\TransactionTrendService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->wallet = Wallet::factory()->forUser($this->user)->withBalance(500000)->create();
    $this->service = app(TransactionTrendService::class);
});

describe('TransactionTrendService Volume Trend', function () {
    it('returns transaction volume trend', function () {
        for ($i = 0; $i < 5; $i++) {
            Transaction::create([
                'wallet_id' => $this->wallet->id,
                'transactionable_type' => Wallet::class,
                'transactionable_id' => $this->wallet->id,
                'type' => TransactionTypeCast::CREDIT,
                'status' => TransactionStatusCast::COMPLETED,
                'amount' => 100000,
                'created_at' => now()->subDays($i * 5),
            ]);
        }

        $result = $this->service->getVolumeTrend(period: 'month');

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('labels')
            ->and($result['data'])->toHaveKey('datasets')
            ->and($result['data']['summary'])->toHaveKey('total_volume')
            ->and($result['data']['summary'])->toHaveKey('total_count')
            ->and($result['data']['summary'])->toHaveKey('average_amount');
    });

    it('filters by wallet when provided', function () {
        // Create transaction for our wallet
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
        ]);

        // Create transaction for another wallet
        $otherWallet = Wallet::factory()->forUser(User::factory()->create())->create();
        Transaction::create([
            'wallet_id' => $otherWallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $otherWallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 200000,
        ]);

        $result = $this->service->getVolumeTrend(
            period: 'month',
            walletId: $this->wallet->id,
            inRupees: true
        );

        expect($result['data']['summary']['total_volume'])->toBe(1000.0);
    });

    it('supports all period options', function () {
        $periods = ['today', 'week', 'month', 'quarter', 'year', 'custom'];

        foreach ($periods as $period) {
            $result = $this->service->getVolumeTrend(
                period: $period,
                startDate: $period === 'custom' ? now()->subDays(7)->toDateString() : null,
                endDate: $period === 'custom' ? now()->toDateString() : null
            );

            expect($result['success'])->toBeTrue()
                ->and($result['meta']['period'])->toBe($period);
        }
    });
});

describe('TransactionTrendService Payment Method Breakdown', function () {
    it('returns breakdown by payment method', function () {
        // Create transactions with different payment methods
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
            'payment_method' => PaymentMethodCast::WALLET,
        ]);

        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 200000,
            'payment_method' => PaymentMethodCast::CASHFREE,
        ]);

        $result = $this->service->getByPaymentMethod(period: 'month');

        expect($result['success'])->toBeTrue()
            ->and($result['data']['labels'])->toBeArray()
            ->and($result['data']['summary']['breakdown'])->toBeArray()
            ->and($result['data']['summary']['total_amount'])->toBeGreaterThan(0);
    });
});

describe('TransactionTrendService Status Distribution', function () {
    it('returns status distribution', function () {
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
        ]);

        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 50000,
        ]);

        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::FAILED,
            'amount' => 30000,
        ]);

        $result = $this->service->getStatusDistribution(period: 'month');

        expect($result['success'])->toBeTrue()
            ->and($result['data']['summary'])->toHaveKey('total')
            ->and($result['data']['summary'])->toHaveKey('success_rate')
            ->and($result['data']['summary'])->toHaveKey('failure_rate')
            ->and($result['data']['summary']['total'])->toBe(3);
    });

    it('calculates success rate correctly', function () {
        // 2 completed, 1 failed = 66.67% success rate
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
        ]);

        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
        ]);

        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::FAILED,
            'amount' => 100000,
        ]);

        $result = $this->service->getStatusDistribution(period: 'month');

        expect($result['data']['summary']['success_rate'])->toBe(66.67)
            ->and($result['data']['summary']['failure_rate'])->toBe(33.33);
    });
});

describe('TransactionTrendService Success Failure Trend', function () {
    it('returns success vs failure trend over time', function () {
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
        ]);

        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::FAILED,
            'amount' => 50000,
        ]);

        $result = $this->service->getSuccessFailureTrend(period: 'month');

        expect($result['success'])->toBeTrue()
            ->and($result['data']['datasets'])->toHaveCount(3) // Completed, Failed, Pending
            ->and($result['data']['summary'])->toHaveKey('completed')
            ->and($result['data']['summary'])->toHaveKey('failed')
            ->and($result['data']['summary'])->toHaveKey('success_rate');
    });
});

describe('TransactionTrendService Fee Collection', function () {
    it('returns fee collection trend', function () {
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
            'fee' => 1000, // 10 Rs
            'tax' => 180,  // 1.8 Rs
        ]);

        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'transactionable_type' => Wallet::class,
            'transactionable_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 200000,
            'fee' => 2000,
            'tax' => 360,
        ]);

        $result = $this->service->getFeeCollectionTrend(
            period: 'month',
            inRupees: true
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['summary'])->toHaveKey('total_fees')
            ->and($result['data']['summary'])->toHaveKey('total_tax')
            ->and($result['data']['summary'])->toHaveKey('total_revenue')
            ->and($result['data']['summary']['total_fees'])->toBe(30.0)
            ->and($result['data']['summary']['total_tax'])->toBe(5.4);
    });
});
