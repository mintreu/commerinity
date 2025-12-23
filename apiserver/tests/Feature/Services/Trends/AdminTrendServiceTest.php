<?php

declare(strict_types=1);

use App\Casts\KycStatusCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Casts\UserTypeCast;
use App\Casts\WalletStatusCast;
use App\Models\Kyc;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Trends\AdminTrendService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(AdminTrendService::class);
});

describe('AdminTrendService User Registration Trend', function () {
    it('returns user registration trend', function () {
        User::factory()->count(5)->create();

        $result = $this->service->getUserRegistrationTrend(period: 'year');

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('labels')
            ->and($result['data'])->toHaveKey('datasets')
            ->and($result['data']['summary'])->toHaveKey('new_users')
            ->and($result['data']['summary'])->toHaveKey('total_users')
            ->and($result['data']['summary'])->toHaveKey('active_users')
            ->and($result['data']['summary'])->toHaveKey('growth_rate');
    });

    it('filters by user type', function () {
        User::factory()->count(3)->create(['type' => UserTypeCast::MEMBER]);
        User::factory()->count(2)->create(['type' => UserTypeCast::REGULAR]);

        $result = $this->service->getUserRegistrationTrend(
            period: 'year',
            userType: 'member'
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['summary']['new_users'])->toBe(3);
    });

    it('supports different periods', function () {
        $periods = ['today', 'week', 'month', 'quarter', 'year'];

        foreach ($periods as $period) {
            $result = $this->service->getUserRegistrationTrend(period: $period);

            expect($result['success'])->toBeTrue()
                ->and($result['meta']['period'])->toBe($period);
        }
    });
});

describe('AdminTrendService Users By Type', function () {
    it('returns user distribution by type', function () {
        User::factory()->count(5)->create(['type' => UserTypeCast::REGULAR]);
        User::factory()->count(3)->create(['type' => UserTypeCast::MEMBER]);
        User::factory()->count(2)->create(['type' => UserTypeCast::PROMOTER]);
        User::factory()->create(['type' => UserTypeCast::ADVISOR]);

        $result = $this->service->getUsersByType(period: 'year');

        expect($result['success'])->toBeTrue()
            ->and($result['data']['datasets'])->toHaveCount(4)
            ->and($result['data']['summary']['distribution'])->toBeArray()
            ->and($result['data']['summary']['total_users'])->toBe(11);
    });
});

describe('AdminTrendService Revenue Trend', function () {
    it('returns platform revenue trend', function () {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->forUser($user)->create();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
            'fee' => 1000,
            'tax' => 180,
        ]);

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 200000,
            'fee' => 2000,
            'tax' => 360,
        ]);

        $result = $this->service->getRevenueTrend(
            period: 'year',
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

describe('AdminTrendService Transaction Volume', function () {
    it('returns platform transaction volume', function () {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->forUser($user)->create();

        for ($i = 0; $i < 5; $i++) {
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => TransactionTypeCast::CREDIT,
                'status' => TransactionStatusCast::COMPLETED,
                'amount' => 100000,
            ]);
        }

        $result = $this->service->getTransactionVolumeTrend(
            period: 'year',
            inRupees: true
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['summary']['total_volume'])->toBe(5000.0)
            ->and($result['data']['summary']['total_transactions'])->toBe(5)
            ->and($result['data']['summary']['average_transaction'])->toBe(1000.0);
    });
});

describe('AdminTrendService KYC Trend', function () {
    it('returns KYC approval trend', function () {
        $user = User::factory()->create();

        Kyc::factory()->create([
            'kycable_type' => User::class,
            'kycable_id' => $user->id,
            'status' => KycStatusCast::APPROVED,
        ]);

        Kyc::factory()->create([
            'kycable_type' => User::class,
            'kycable_id' => $user->id,
            'status' => KycStatusCast::PENDING,
        ]);

        Kyc::factory()->create([
            'kycable_type' => User::class,
            'kycable_id' => $user->id,
            'status' => KycStatusCast::REJECTED,
        ]);

        $result = $this->service->getKycTrend(period: 'year');

        expect($result['success'])->toBeTrue()
            ->and($result['data']['datasets'])->toHaveCount(3)
            ->and($result['data']['summary'])->toHaveKey('submitted')
            ->and($result['data']['summary'])->toHaveKey('approved')
            ->and($result['data']['summary'])->toHaveKey('rejected')
            ->and($result['data']['summary'])->toHaveKey('approval_rate');
    });
});

describe('AdminTrendService Wallet Health', function () {
    it('returns wallet health metrics', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Wallet::factory()->forUser($user1)->create([
            'balance' => 500000, // 5000 Rs
            'hold_balance' => 10000,
            'status' => WalletStatusCast::ACTIVE,
        ]);

        Wallet::factory()->forUser($user2)->create([
            'balance' => 0,
            'status' => WalletStatusCast::SUSPENDED,
        ]);

        $result = $this->service->getWalletHealth(inRupees: true);

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('total_wallets')
            ->and($result['data'])->toHaveKey('active_wallets')
            ->and($result['data'])->toHaveKey('suspended_wallets')
            ->and($result['data'])->toHaveKey('total_balance')
            ->and($result['data'])->toHaveKey('zero_balance_count')
            ->and($result['data'])->toHaveKey('balance_distribution')
            ->and($result['data']['total_wallets'])->toBe(2)
            ->and($result['data']['active_wallets'])->toBe(1)
            ->and($result['data']['suspended_wallets'])->toBe(1)
            ->and($result['data']['zero_balance_count'])->toBe(1);
    });
});

describe('AdminTrendService Platform Overview', function () {
    it('returns comprehensive platform overview', function () {
        $user = User::factory()->create(['type' => UserTypeCast::MEMBER]);
        $wallet = Wallet::factory()->forUser($user)->withBalance(500000)->create();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::COMPLETED,
            'amount' => 100000,
            'fee' => 1000,
            'tax' => 180,
        ]);

        Kyc::factory()->create([
            'kycable_type' => User::class,
            'kycable_id' => $user->id,
            'status' => KycStatusCast::PENDING,
        ]);

        $result = $this->service->getPlatformOverview(inRupees: true);

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('users')
            ->and($result['data'])->toHaveKey('transactions')
            ->and($result['data'])->toHaveKey('revenue')
            ->and($result['data'])->toHaveKey('wallets')
            ->and($result['data'])->toHaveKey('kyc')
            ->and($result['data']['users']['total'])->toBe(1)
            ->and($result['data']['users']['active_members'])->toBe(1)
            ->and($result['data']['transactions']['total_count'])->toBe(1)
            ->and($result['data']['kyc']['pending'])->toBe(1);
    });
});
