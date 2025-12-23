<?php

declare(strict_types=1);

use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Trends\WalletTrendService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->wallet = Wallet::factory()->forUser($this->user)->withBalance(500000)->create();
    $this->service = app(WalletTrendService::class);
});

describe('WalletTrendService Balance History', function () {
    it('returns balance history for wallet', function () {
        // Create transactions over the past month
        for ($i = 0; $i < 5; $i++) {
            Transaction::create([
                'wallet_id' => $this->wallet->id,
                'type' => TransactionTypeCast::CREDIT,
                'status' => TransactionStatusCast::COMPLETED,
                'amount' => 100000,
                'balance_after' => 500000 + (($i + 1) * 100000),
                'created_at' => now()->subDays($i * 5),
            ]);
        }

        $result = $this->service->getBalanceHistory(
            walletId: $this->wallet->id,
            period: 'month'
        );

        expect($result)
            ->toHaveKey('success')
            ->toHaveKey('data')
            ->toHaveKey('meta')
            ->and($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('labels')
            ->and($result['data'])->toHaveKey('datasets')
            ->and($result['data'])->toHaveKey('summary')
            ->and($result['meta']['period'])->toBe('month');
    });

    it('supports different periods', function () {
        $periods = ['today', 'week', 'month', 'quarter', 'year'];

        foreach ($periods as $period) {
            $result = $this->service->getBalanceHistory(
                walletId: $this->wallet->id,
                period: $period
            );

            expect($result['success'])->toBeTrue()
                ->and($result['meta']['period'])->toBe($period);
        }
    });

    it('supports custom date range', function () {
        $result = $this->service->getBalanceHistory(
            walletId: $this->wallet->id,
            period: 'custom',
            startDate: now()->subDays(30)->toDateString(),
            endDate: now()->toDateString()
        );

        expect($result['success'])->toBeTrue()
            ->and($result['meta']['period'])->toBe('custom')
            ->and($result['data']['summary'])->toHaveKey('period_start')
            ->and($result['data']['summary'])->toHaveKey('period_end');
    });

    it('converts amounts to rupees when requested', function () {
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000, // 1000 Rs in paisa
            'balance_after' => 600000,
        ]);

        $resultInRupees = $this->service->getBalanceHistory(
            walletId: $this->wallet->id,
            period: 'month',
            inRupees: true
        );

        $resultInPaisa = $this->service->getBalanceHistory(
            walletId: $this->wallet->id,
            period: 'month',
            inRupees: false
        );

        // Current balance should be in rupees (5000) vs paisa (500000)
        expect($resultInRupees['data']['summary']['current_balance'])->toBe(5000.0);
    });
});

describe('WalletTrendService Credit Debit Trend', function () {
    it('returns credit vs debit comparison', function () {
        // Create credit transactions
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
        ]);

        // Create debit transaction
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'type' => TransactionTypeCast::DEBIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 50000,
        ]);

        $result = $this->service->getCreditDebitTrend(
            walletId: $this->wallet->id,
            period: 'month'
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['datasets'])->toHaveCount(2)
            ->and($result['data']['summary'])->toHaveKey('total_credits')
            ->and($result['data']['summary'])->toHaveKey('total_debits')
            ->and($result['data']['summary'])->toHaveKey('net_change');
    });

    it('calculates net change correctly', function () {
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 200000, // 2000 Rs
        ]);

        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'type' => TransactionTypeCast::DEBIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 50000, // 500 Rs
        ]);

        $result = $this->service->getCreditDebitTrend(
            walletId: $this->wallet->id,
            period: 'month',
            inRupees: true
        );

        expect($result['data']['summary']['total_credits'])->toBe(2000.0)
            ->and($result['data']['summary']['total_debits'])->toBe(500.0)
            ->and($result['data']['summary']['net_change'])->toBe(1500.0);
    });
});

describe('WalletTrendService Activity Volume', function () {
    it('returns activity volume with count', function () {
        for ($i = 0; $i < 3; $i++) {
            Transaction::create([
                'wallet_id' => $this->wallet->id,
                'type' => TransactionTypeCast::CREDIT,
                'status' => TransactionStatusCast::COMPLETED,
                'amount' => 100000,
            ]);
        }

        $result = $this->service->getActivityVolume(
            walletId: $this->wallet->id,
            period: 'month'
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['summary']['total_transactions'])->toBe(3)
            ->and($result['data']['summary'])->toHaveKey('average_transaction');
    });
});

describe('WalletTrendService Comparison Stats', function () {
    it('returns current vs previous period comparison', function () {
        // Create transactions in current month
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
            'created_at' => now(),
        ]);

        // Create transactions in previous month
        Transaction::create([
            'wallet_id' => $this->wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 50000,
            'created_at' => now()->subMonth(),
        ]);

        $result = $this->service->getComparisonStats(
            walletId: $this->wallet->id,
            period: 'month'
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('current')
            ->and($result['data'])->toHaveKey('previous')
            ->and($result['data'])->toHaveKey('changes')
            ->and($result['data']['changes'])->toHaveKey('credits_change');
    });
});
