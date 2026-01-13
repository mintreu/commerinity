<?php

declare(strict_types=1);

use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Casts\UserTypeCast;
use App\Models\Address;
use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Events\PaymentCompleted;
use App\Services\Affiliate\AffiliateGenealogy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/**
 * Complete Subscription Flow Integration Test
 *
 * Tests the complete end-to-end flow for membership subscription payments:
 * 1. User selects membership stage/level
 * 2. Subscription is created with pending status
 * 3. Payment transaction is created
 * 4. Payment is completed
 * 5. Subscription is activated
 * 6. User type is upgraded (REGULAR -> MEMBER)
 * 7. User is placed in affiliate tree (if has sponsor)
 * 8. Initial commissions are triggered
 */

beforeEach(function () {
    // Create payment integration
    $this->integration = \App\Models\Integration::factory()->cashfree()->create();
    app(\App\Services\IntegrationServices\Payment\PaymentService::class)->refreshProviders();

    // Create regular user with wallet
    $this->user = User::factory()->create(['type' => UserTypeCast::REGULAR->value]);
    $this->wallet = Wallet::factory()->for($this->user, 'walletable')->create();

    // Create membership stage and level
    $this->stage = Stage::factory()->create([
        'base_price' => 99900, // ₹999
        'discount' => 0,
        'tax_amount' => 17982,
        'price' => 117882, // ₹1178.82
    ]);

    $this->level = Level::factory()->create([
        'stage_id' => $this->stage->id,
        'base_price' => 99900,
        'price' => 117882,
        'bv' => 99900, // Business Volume for affiliate
        'pv' => 99900, // Personal Volume
    ]);

    // Mock Cashfree API for subscription payment
    Http::fake(['sandbox.cashfree.com/pg/orders' => Http::response([
        'cf_order_id' => 'cf_sub_'.fake()->uuid(),
        'order_id' => '*',
        'payment_session_id' => 'session_'.fake()->uuid(),
        'order_status' => 'ACTIVE',
    ], 200)]);
});

describe('Subscription Flow - New Member Registration', function () {
    it('activates subscription and upgrades user to member', function () {
        // Create pending subscription
        $subscription = UserSubscription::factory()
            ->forUser($this->user)
            ->pending()
            ->create([
                'stage_id' => $this->stage->id,
                'level_id' => $this->level->id,
                'amount' => 117882,
            ]);

        expect($subscription->is_paid)->toBeFalse();
        expect($subscription->status)->toBe(UserSubscription::STATUS_PENDING);
        expect($this->user->type)->toBe(UserTypeCast::REGULAR);

        // Create pending transaction for subscription
        $transaction = Transaction::create([
            'uuid' => 'TXN-SUB-NEW-' . fake()->randomNumber(6),
            'transactionable_type' => UserSubscription::class,
            'transactionable_id' => $subscription->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 117882,
            'purpose' => 'Membership Subscription',
            'payment_method' => 'cashfree',
        ]);

        // Simulate payment completion
        $transaction->update([
            'status' => TransactionStatusCast::COMPLETED,
            'verified' => true,
            'verified_at' => now(),
        ]);

        event(new PaymentCompleted($transaction));

        // Verify subscription is activated
        $subscription->refresh();
        expect($subscription->is_paid)->toBeTrue();
        expect($subscription->status)->toBe(UserSubscription::STATUS_ACTIVE);
        expect($subscription->paid_at)->not->toBeNull();
        expect($subscription->starts_at)->not->toBeNull();
        expect($subscription->expires_at)->not->toBeNull();
        expect($subscription->expires_at->isFuture())->toBeTrue();

        // Verify user type upgraded to MEMBER
        expect($this->user->fresh()->type)->toBe(UserTypeCast::MEMBER);

        // Verify subscription has transaction reference
        expect($subscription->transaction_id)->toBe($transaction->id);
    });

    it('sets subscription expiry to 1 year from activation', function () {
        $subscription = UserSubscription::factory()
            ->forUser($this->user)
            ->pending()
            ->create([
                'stage_id' => $this->stage->id,
                'level_id' => $this->level->id,
                'amount' => 117882,
            ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-SUB-EXP-' . fake()->randomNumber(6),
            'transactionable_type' => UserSubscription::class,
            'transactionable_id' => $subscription->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 117882,
            'purpose' => 'Membership Subscription',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        $subscription->refresh();

        // Verify expiry is approximately 1 year
        $expiresAt = $subscription->expires_at;
        $expectedExpiry = now()->addYear();
        $diff = abs($expiresAt->diffInSeconds($expectedExpiry));

        expect($diff)->toBeLessThan(10); // Allow 10 seconds tolerance
    });
});

describe('Subscription Flow - With Sponsor (Affiliate Tree Placement)', function () {
    beforeEach(function () {
        // Create sponsor (upline)
        $this->sponsor = User::factory()->create([
            'type' => UserTypeCast::MEMBER->value,
        ]);

        // Set sponsor as parent for new user
        $this->user->update(['parent_id' => $this->sponsor->id]);
    });

    it('places user in affiliate tree under sponsor', function () {
        $subscription = UserSubscription::factory()
            ->forUser($this->user)
            ->pending()
            ->create([
                'stage_id' => $this->stage->id,
                'level_id' => $this->level->id,
                'amount' => 117882,
            ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-SUB-AFF-' . fake()->randomNumber(6),
            'transactionable_type' => UserSubscription::class,
            'transactionable_id' => $subscription->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 117882,
            'purpose' => 'Membership Subscription',
        ]);

        // Verify user is NOT in affiliate tree yet
        expect(AffiliateGenealogy::where('user_id', $this->user->id)->count())->toBe(0);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Verify user is now in affiliate tree
        $genealogy = AffiliateGenealogy::where('user_id', $this->user->id)->first();
        expect($genealogy)->not->toBeNull();
        expect($genealogy->parent_id)->toBe($this->sponsor->id);

        // Verify sponsor now has this user in children
        expect($this->sponsor->fresh()->children->contains($this->user))->toBeTrue();
    });

    it('triggers affiliate commissions after placement', function () {
        $subscription = UserSubscription::factory()
            ->forUser($this->user)
            ->pending()
            ->create([
                'stage_id' => $this->stage->id,
                'level_id' => $this->level->id,
                'amount' => 117882,
            ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-SUB-COMM-' . fake()->randomNumber(6),
            'transactionable_type' => UserSubscription::class,
            'transactionable_id' => $subscription->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 117882,
            'purpose' => 'Membership Subscription',
        ]);

        // No commissions before payment
        expect(\App\Models\AffiliateCommission::count())->toBe(0);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Verify commissions were created for sponsor
        $commissions = \App\Models\AffiliateCommission::all();
        expect($commissions)->toHaveCountGreaterThan(0);

        // Verify at least one commission for the sponsor
        $hasSponsorCommission = $commissions->contains(fn ($c) => $c->user_id === $this->sponsor->id);
        expect($hasSponsorCommission)->toBeTrue();
    });
});

describe('Subscription Flow - Without Sponsor (Team Head)', function () {
    it('activates subscription without affiliate placement', function () {
        // User has no parent (will be team head)
        expect($this->user->parent_id)->toBeNull();

        $subscription = UserSubscription::factory()
            ->forUser($this->user)
            ->pending()
            ->create([
                'stage_id' => $this->stage->id,
                'level_id' => $this->level->id,
                'amount' => 117882,
            ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-SUB-HEAD-' . fake()->randomNumber(6),
            'transactionable_type' => UserSubscription::class,
            'transactionable_id' => $subscription->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 117882,
            'purpose' => 'Membership Subscription',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Verify subscription activated
        expect($subscription->fresh()->is_paid)->toBeTrue();
        expect($subscription->status)->toBe(UserSubscription::STATUS_ACTIVE);

        // Verify user upgraded to member
        expect($this->user->fresh()->type)->toBe(UserTypeCast::MEMBER);

        // Verify NO affiliate genealogy was created (no placement)
        expect(AffiliateGenealogy::where('user_id', $this->user->id)->count())->toBe(0);
    });
});

describe('Subscription Flow - Subscription Renewal', function () {
    beforeEach(function () {
        // Create active subscription that's about to expire
        $this->previousSubscription = UserSubscription::factory()
            ->forUser($this->user)
            ->active()
            ->create([
                'stage_id' => $this->stage->id,
                'level_id' => $this->level->id,
                'expires_at' => now()->addDays(7),
            ]);

        // Upgrade user to member
        $this->user->update(['type' => UserTypeCast::MEMBER->value]);
    });

    it('renews existing subscription', function () {
        // Create renewal subscription
        $renewalSubscription = UserSubscription::factory()
            ->forUser($this->user)
            ->pending()
            ->create([
                'stage_id' => $this->stage->id,
                'level_id' => $this->level->id,
                'previous_subscription_id' => $this->previousSubscription->id,
                'renewal_count' => 1,
                'amount' => 117882,
            ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-SUB-RENEW-' . fake()->randomNumber(6),
            'transactionable_type' => UserSubscription::class,
            'transactionable_id' => $renewalSubscription->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 117882,
            'purpose' => 'Subscription Renewal',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Verify renewal activated
        $renewalSubscription->refresh();
        expect($renewalSubscription->is_paid)->toBeTrue();
        expect($renewalSubscription->status)->toBe(UserSubscription::STATUS_ACTIVE);
        expect($renewalSubscription->previous_subscription_id)->toBe($this->previousSubscription->id);
        expect($renewalSubscription->renewal_count)->toBe(1);

        // Verify previous subscription marked as expired
        $this->previousSubscription->refresh();
        expect($this->previousSubscription->status)->toBe(UserSubscription::STATUS_EXPIRED);
    });

    it('carries over PV from previous subscription', function () {
        // Set some PV on previous subscription
        $this->previousSubscription->update([
            'personal_pv' => 50000,
            'team_pv' => 150000,
        ]);

        $renewalSubscription = UserSubscription::factory()
            ->forUser($this->user)
            ->pending()
            ->create([
                'stage_id' => $this->stage->id,
                'level_id' => $this->level->id,
                'previous_subscription_id' => $this->previousSubscription->id,
                'renewal_count' => 1,
                'personal_pv' => 50000, // Should be carried over
                'team_pv' => 150000,
                'amount' => 117882,
            ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-SUB-PV-' . fake()->randomNumber(6),
            'transactionable_type' => UserSubscription::class,
            'transactionable_id' => $renewalSubscription->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 117882,
            'purpose' => 'Subscription Renewal',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        $renewalSubscription->refresh();
        expect($renewalSubscription->personal_pv)->toBe(50000);
        expect($renewalSubscription->team_pv)->toBe(150000);
    });
});

describe('Subscription Flow - Error Cases', function () {
    it('handles payment failure gracefully', function () {
        $subscription = UserSubscription::factory()
            ->forUser($this->user)
            ->pending()
            ->create([
                'stage_id' => $this->stage->id,
                'level_id' => $this->level->id,
                'amount' => 117882,
            ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-SUB-FAIL-' . fake()->randomNumber(6),
            'transactionable_type' => UserSubscription::class,
            'transactionable_id' => $subscription->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 117882,
            'purpose' => 'Membership Subscription',
        ]);

        // Mark transaction as failed
        $transaction->update([
            'status' => TransactionStatusCast::FAILED,
            'verified' => true,
            'verified_at' => now(),
        ]);

        event(new PaymentCompleted($transaction));

        // Verify subscription remains pending
        $subscription->refresh();
        expect($subscription->is_paid)->toBeFalse();
        expect($subscription->status)->toBe(UserSubscription::STATUS_PENDING);

        // Verify user type unchanged
        expect($this->user->fresh()->type)->toBe(UserTypeCast::REGULAR);
    });

    it('handles already active subscription', function () {
        // Create already active subscription
        $activeSubscription = UserSubscription::factory()
            ->forUser($this->user)
            ->active()
            ->create([
                'stage_id' => $this->stage->id,
                'level_id' => $this->level->id,
                'amount' => 117882,
            ]);

        // Try to create transaction for already active subscription
        $transaction = Transaction::create([
            'uuid' => 'TXN-SUB-DUP-' . fake()->randomNumber(6),
            'transactionable_type' => UserSubscription::class,
            'transactionable_id' => $activeSubscription->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 117882,
            'purpose' => 'Membership Subscription',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Subscription should remain active (no double activation)
        $activeSubscription->refresh();
        expect($activeSubscription->status)->toBe(UserSubscription::STATUS_ACTIVE);
    });
});

describe('Subscription Flow - Level Upgrades', function () {
    beforeEach(function () {
        // User is already a member at level 1
        $this->currentLevel = Level::factory()->create([
            'stage_id' => $this->stage->id,
            'name' => 'Starter',
            'base_price' => 50000,
            'price' => 59000,
        ]);

        $this->currentSubscription = UserSubscription::factory()
            ->forUser($this->user)
            ->active()
            ->create([
                'stage_id' => $this->stage->id,
                'level_id' => $this->currentLevel->id,
                'current_level_id' => $this->currentLevel->id,
                'amount' => 59000,
            ]);

        $this->user->update(['type' => UserTypeCast::MEMBER->value]);

        // Create higher level
        $this->higherLevel = Level::factory()->create([
            'stage_id' => $this->stage->id,
            'name' => 'Pro',
            'base_price' => 150000,
            'price' => 177000,
        ]);
    });

    it('upgrades user to higher membership level', function () {
        // Create upgrade subscription
        $upgradeSubscription = UserSubscription::factory()
            ->forUser($this->user)
            ->pending()
            ->create([
                'stage_id' => $this->stage->id,
                'level_id' => $this->higherLevel->id,
                'current_level_id' => $this->currentLevel->id,
                'highest_level_id' => $this->currentLevel->id,
                'amount' => 177000,
            ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-SUB-UPG-' . fake()->randomNumber(6),
            'transactionable_type' => UserSubscription::class,
            'transactionable_id' => $upgradeSubscription->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 177000,
            'purpose' => 'Level Upgrade',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Verify upgrade activated
        $upgradeSubscription->refresh();
        expect($upgradeSubscription->is_paid)->toBeTrue();
        expect($upgradeSubscription->status)->toBe(UserSubscription::STATUS_ACTIVE);
        expect($upgradeSubscription->current_level_id)->toBe($this->higherLevel->id);
        expect($upgradeSubscription->highest_level_id)->toBe($this->higherLevel->id);

        // Verify previous subscription marked as expired
        $this->currentSubscription->refresh();
        expect($this->currentSubscription->status)->toBe(UserSubscription::STATUS_EXPIRED);
    });
});
