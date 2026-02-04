<?php

declare(strict_types=1);

use App\Casts\CommissionStatusCast;
use App\Casts\CommissionTypeCast;
use App\Models\Affiliate\AffiliateCommission;
use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('AffiliateCommission Model', function () {

    it('can be created with factory', function () {
        $user = User::factory()->create();
        $commission = AffiliateCommission::factory()->forUser($user)->create();

        expect($commission)->toBeInstanceOf(AffiliateCommission::class)
            ->and($commission->user_id)->toBe($user->id)
            ->and($commission->uuid)->toStartWith('COM-');
    });

    it('auto-generates uuid and dates on creation', function () {
        $user = User::factory()->create();
        $commission = AffiliateCommission::factory()->forUser($user)->create();

        expect($commission->uuid)->toStartWith('COM-')
            ->and($commission->commission_date)->not->toBeNull()
            ->and($commission->period_key)->toBe(now()->format('Y-m'));
    });

    it('calculates net amount from gross minus deductions', function () {
        $user = User::factory()->create();
        $commission = AffiliateCommission::factory()
            ->forUser($user)
            ->withAmounts(100000, 10000, 5000) // gross, tds, admin_fee
            ->create();

        expect($commission->gross_amount)->toBe(100000)
            ->and($commission->tds_amount)->toBe(10000)
            ->and($commission->admin_fee)->toBe(5000)
            ->and($commission->net_amount)->toBe(85000);
    });

    it('can create sponsor bonus commission', function () {
        $user = User::factory()->create();
        $commission = AffiliateCommission::factory()
            ->forUser($user)
            ->sponsorBonus()
            ->create();

        expect($commission->type->value)->toBe(CommissionTypeCast::SPONSOR_BONUS->value);
    });

    it('can create level commission with rate', function () {
        $user = User::factory()->create();
        $commission = AffiliateCommission::factory()
            ->forUser($user)
            ->levelCommission(2, 5.0)
            ->create();

        expect($commission->type->value)->toBe(CommissionTypeCast::LEVEL_COMMISSION->value)
            ->and($commission->level)->toBe(2)
            ->and($commission->rate_percent)->toBe('5.00');
    });

    it('belongs to a user', function () {
        $user = User::factory()->create();
        $commission = AffiliateCommission::factory()->forUser($user)->create();

        expect($commission->user)->toBeInstanceOf(User::class)
            ->and($commission->user->id)->toBe($user->id);
    });

    it('can have from_user relationship', function () {
        $user = User::factory()->create();
        $fromUser = User::factory()->create();

        $commission = AffiliateCommission::factory()
            ->forUser($user)
            ->fromUser($fromUser)
            ->create();

        expect($commission->fromUser)->toBeInstanceOf(User::class)
            ->and($commission->fromUser->id)->toBe($fromUser->id);
    });

    it('can have genealogy relationship', function () {
        $user = User::factory()->create();
        $genealogy = AffiliateGenealogy::factory()->forUser($user)->create();

        $commission = AffiliateCommission::factory()
            ->withGenealogy($genealogy)
            ->create();

        expect($commission->genealogy)->toBeInstanceOf(AffiliateGenealogy::class)
            ->and($commission->genealogy->id)->toBe($genealogy->id);
    });

    it('can be approved', function () {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $commission = AffiliateCommission::factory()
            ->forUser($user)
            ->pending()
            ->create();

        expect($commission->status->value)->toBe(CommissionStatusCast::PENDING->value);

        $commission->approve($admin->id);

        expect($commission->status->value)->toBe(CommissionStatusCast::APPROVED->value)
            ->and($commission->approved_by)->toBe($admin->id)
            ->and($commission->approved_at)->not->toBeNull();
    });

    it('can be marked as paid', function () {
        $user = User::factory()->create();
        $wallet = \App\Models\Wallet::factory()->forUser($user)->create();
        $transaction = \App\Models\Transaction::factory()->forWallet($wallet)->create();

        $commission = AffiliateCommission::factory()
            ->forUser($user)
            ->approved()
            ->create();

        $commission->markPaid($transaction->id);

        expect($commission->status->value)->toBe(CommissionStatusCast::PAID->value)
            ->and($commission->paid_via_transaction_id)->toBe($transaction->id)
            ->and($commission->paid_at)->not->toBeNull();
    });

    it('can be put on hold', function () {
        $user = User::factory()->create();
        $commission = AffiliateCommission::factory()
            ->forUser($user)
            ->pending()
            ->create();

        $commission->hold('Under review');

        expect($commission->status->value)->toBe(CommissionStatusCast::HELD->value)
            ->and($commission->description)->toContain('Under review');
    });

    it('can be cancelled', function () {
        $user = User::factory()->create();
        $commission = AffiliateCommission::factory()
            ->forUser($user)
            ->pending()
            ->create();

        $commission->cancel('Order cancelled');

        expect($commission->status->value)->toBe(CommissionStatusCast::CANCELLED->value);
    });

    it('can be reversed (creates reversal entry)', function () {
        $user = User::factory()->create();
        $commission = AffiliateCommission::factory()
            ->forUser($user)
            ->paid()
            ->withAmounts(10000)
            ->create();

        $reversal = $commission->reverse('Refund requested');

        // Reversal uses POSITIVE amounts (type=reversal indicates clawback)
        expect($commission->status->value)->toBe(CommissionStatusCast::REVERSED->value)
            ->and($reversal->type->value)->toBe(CommissionTypeCast::REVERSAL->value)
            ->and($reversal->gross_amount)->toBe(10000) // Positive amount being reversed
            ->and($reversal->reversed_commission_id)->toBe($commission->id);
    });

    it('throws exception when approving non-pending commission', function () {
        $user = User::factory()->create();
        $commission = AffiliateCommission::factory()
            ->forUser($user)
            ->paid()
            ->create();

        expect(fn () => $commission->approve(1))
            ->toThrow(RuntimeException::class);
    });

    it('throws exception when reversing non-paid commission', function () {
        $user = User::factory()->create();
        $commission = AffiliateCommission::factory()
            ->forUser($user)
            ->pending()
            ->create();

        expect(fn () => $commission->reverse())
            ->toThrow(RuntimeException::class);
    });

    it('calculates total earnings for user', function () {
        $user = User::factory()->create();

        // Create some paid commissions
        AffiliateCommission::factory()
            ->forUser($user)
            ->paid()
            ->withAmounts(10000)
            ->count(3)
            ->create();

        // Create pending (should not count)
        AffiliateCommission::factory()
            ->forUser($user)
            ->pending()
            ->withAmounts(10000)
            ->create();

        $total = AffiliateCommission::getTotalEarnings($user->id);

        expect($total)->toBe(30000);
    });

    it('calculates pending earnings for user', function () {
        $user = User::factory()->create();

        AffiliateCommission::factory()
            ->forUser($user)
            ->pending()
            ->withAmounts(10000)
            ->count(2)
            ->create();

        AffiliateCommission::factory()
            ->forUser($user)
            ->approved()
            ->withAmounts(5000)
            ->create();

        $pending = AffiliateCommission::getPendingEarnings($user->id);

        expect($pending)->toBe(25000);
    });

    it('scope forUser works', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        AffiliateCommission::factory()->forUser($user1)->count(3)->create();
        AffiliateCommission::factory()->forUser($user2)->count(2)->create();

        expect(AffiliateCommission::forUser($user1->id)->count())->toBe(3);
    });

    it('scope ofType works', function () {
        $user = User::factory()->create();

        AffiliateCommission::factory()->forUser($user)->sponsorBonus()->count(2)->create();
        AffiliateCommission::factory()->forUser($user)->levelCommission()->count(3)->create();

        expect(AffiliateCommission::ofType(CommissionTypeCast::SPONSOR_BONUS->value)->count())->toBe(2);
    });

    it('scope forPeriod works', function () {
        $user = User::factory()->create();

        AffiliateCommission::factory()->forUser($user)->thisMonth()->count(2)->create();
        AffiliateCommission::factory()->forUser($user)->lastMonth()->count(1)->create();

        expect(AffiliateCommission::forPeriod(now()->format('Y-m'))->count())->toBe(2);
    });

    it('uses route key uuid', function () {
        $user = User::factory()->create();
        $commission = AffiliateCommission::factory()->forUser($user)->create();

        expect($commission->getRouteKeyName())->toBe('uuid');
    });
});
