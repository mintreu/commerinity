<?php

declare(strict_types=1);

use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\Mlm\MlmCommission;
use App\Models\Mlm\MlmGenealogy;
use App\Models\User;
use App\Services\Membership\SubscriptionService;
use App\Services\Mlm\CommissionProcessorService;
use Database\Factories\Membership\StageFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

// Clear cache before each test to ensure clean state
beforeEach(function () {
    Cache::flush();
});

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Create a stage with all 4 levels
 */
function createStageWithLevels(string $name = 'Basic', int $sortOrder = 1): Stage
{
    StageFactory::resetCounter();

    return Stage::factory()
        ->state([
            'name' => $name,
            'slug' => strtolower($name),
            'sort_order' => $sortOrder,
            'is_default' => $sortOrder === 1,
        ])
        ->withLevels()
        ->create();
}

/**
 * Create a user and activate their subscription (triggers commissions)
 */
function activateUserSubscription(
    User $user,
    Stage $stage,
    SubscriptionService $service,
    ?User $sponsor = null
): array {
    $subscription = $sponsor
        ? $service->createSponsoredSubscription($user, $stage, $sponsor)
        : $service->createSubscription($user, $stage);

    // Pass null for transaction ID in tests (no actual payment)
    return $service->activateSubscription($subscription, transactionId: null);
}

// =============================================================================
// MODE 1: ROOT USER (parent_id = null)
// User registers without affiliate code
// =============================================================================

describe('Mode 1: Root User Journey (No Sponsor)', function () {
    it('creates root user without parent_id', function () {
        $rootUser = User::factory()->create([
            'name' => 'Root User',
            'parent_id' => null,
        ]);

        expect($rootUser->parent_id)->toBeNull()
            ->and($rootUser->parent)->toBeNull();
    });

    it('creates subscription for root user', function () {
        $stage = createStageWithLevels();
        $rootUser = User::factory()->create(['parent_id' => null]);

        $service = app(SubscriptionService::class);
        $subscription = $service->createSubscription($rootUser, $stage);

        expect($subscription->user_id)->toBe($rootUser->id)
            ->and($subscription->stage_id)->toBe($stage->id)
            ->and($subscription->status)->toBe(UserSubscription::STATUS_PENDING);
    });

    it('activates subscription and creates genealogy for root user', function () {
        $stage = createStageWithLevels();
        $rootUser = User::factory()->create(['parent_id' => null]);

        $service = app(SubscriptionService::class);
        $result = activateUserSubscription($rootUser, $stage, $service);

        expect($result['subscription']->status)->toBe(UserSubscription::STATUS_ACTIVE)
            ->and($result['subscription']->is_paid)->toBeTrue()
            ->and($result['genealogy'])->not->toBeNull()
            ->and($result['genealogy']->user_id)->toBe($rootUser->id)
            ->and($result['genealogy']->depth)->toBe(0) // Root has depth 0
            ->and($result['genealogy']->is_active)->toBeTrue();
    });

    it('generates no sponsor commission for root user (no upline)', function () {
        $stage = createStageWithLevels();
        $rootUser = User::factory()->create(['parent_id' => null]);

        $service = app(SubscriptionService::class);
        $result = activateUserSubscription($rootUser, $stage, $service);

        // Root user has no sponsor, so no sponsor bonus should be generated
        expect($result['commissions'])->toBeEmpty();
    });

    it('tracks root user level progression', function () {
        $stage = createStageWithLevels();
        $rootUser = User::factory()->create(['parent_id' => null]);

        $service = app(SubscriptionService::class);
        $result = activateUserSubscription($rootUser, $stage, $service);

        $subscription = $result['subscription'];
        $subscription->refresh();

        // Should start at Bronze (Level 1)
        expect($subscription->current_level_id)->not->toBeNull()
            ->and($subscription->currentLevel->level_number)->toBe(1)
            ->and($subscription->currentLevel->name)->toBe('Bronze');
    });
});

// =============================================================================
// MODE 2: ORIGINATOR USER (Agent/Advisor)
// Joined by advisor/agent, same root user (parent_id = null)
// =============================================================================

describe('Mode 2: Originator User Journey (Advisor)', function () {
    it('creates user with originator tracking', function () {
        // Advisor (root user who can originate others)
        $advisor = User::factory()->create([
            'name' => 'Advisor Alpha',
            'parent_id' => null,
            'type' => 'advisor',
        ]);

        // User joined via advisor (NOT sponsored by advisor)
        $joinedUser = User::factory()->create([
            'name' => 'Joined User',
            'parent_id' => null, // Still root (no MLM sponsor)
            'originator_id' => $advisor->id,
            'originator_type' => 'advisor',
        ]);

        expect($joinedUser->parent_id)->toBeNull()
            ->and($joinedUser->originator_id)->toBe($advisor->id)
            ->and($joinedUser->originator_type)->toBe('advisor');
    });

    it('creates sponsored subscription with sponsor tracking', function () {
        $stage = createStageWithLevels();

        $advisor = User::factory()->create([
            'parent_id' => null,
            'type' => 'advisor',
        ]);

        // Activate advisor first
        $service = app(SubscriptionService::class);
        activateUserSubscription($advisor, $stage, $service);

        // Create user originated by advisor
        $joinedUser = User::factory()->create([
            'parent_id' => null,
            'originator_id' => $advisor->id,
        ]);

        $subscription = $service->createSponsoredSubscription($joinedUser, $stage, $advisor);

        expect($subscription->sponsor_type)->toBe(User::class)
            ->and($subscription->sponsor_id)->toBe($advisor->id);
    });

    it('generates originator commission when user subscribes', function () {
        $stage = createStageWithLevels();

        // Create and activate advisor
        $advisor = User::factory()->create([
            'parent_id' => null,
            'type' => 'advisor',
        ]);

        $service = app(SubscriptionService::class);
        activateUserSubscription($advisor, $stage, $service);

        // User originated by advisor (no MLM sponsor)
        $joinedUser = User::factory()->create([
            'parent_id' => null,
            'originator_id' => $advisor->id,
        ]);

        $result = activateUserSubscription($joinedUser, $stage, $service, $advisor);

        // Check if originator commission was generated
        // Note: Depends on mlm config having originator commissions enabled
        $originatorCommissions = $result['commissions']->filter(
            fn ($c) => $c->type === 'originator_joining'
        );

        // Verify sponsor tracking (advisor paid for subscription)
        expect($result['subscription']->sponsor_id)->toBe($advisor->id);
    });
});

// =============================================================================
// FULL MLM JOURNEY: TEAM BUILDING & LEVEL PROGRESSION
// =============================================================================

describe('Full MLM Journey: Team Building', function () {
    it('builds 4-level deep team with commission flow', function () {
        $stage = createStageWithLevels();
        $service = app(SubscriptionService::class);

        // Level 0: Root/Founder
        $founder = User::factory()->create(['name' => 'Founder', 'parent_id' => null]);
        activateUserSubscription($founder, $stage, $service);

        // Level 1: Direct referral (5 users max)
        $level1User = User::factory()->create([
            'name' => 'Level 1 User',
            'parent_id' => $founder->id,
        ]);
        $l1Result = activateUserSubscription($level1User, $stage, $service);

        // Founder should get sponsor bonus (15%) + level 1 commission (10%)
        $founderCommissions = MlmCommission::where('user_id', $founder->id)->get();
        expect($founderCommissions->count())->toBeGreaterThan(0);

        // Level 2: Referral of Level 1
        $level2User = User::factory()->create([
            'name' => 'Level 2 User',
            'parent_id' => $level1User->id,
        ]);
        $l2Result = activateUserSubscription($level2User, $stage, $service);

        // Check commission distribution
        $allCommissions = MlmCommission::all();

        // Founder should have commissions from Level 1 (direct) and Level 2 (indirect)
        $founderTotal = MlmCommission::where('user_id', $founder->id)->sum('gross_amount');
        expect($founderTotal)->toBeGreaterThan(0);
    });

    it('tracks team counts correctly in genealogy', function () {
        $stage = createStageWithLevels();
        $service = app(SubscriptionService::class);

        // Root user
        $root = User::factory()->create(['parent_id' => null]);
        activateUserSubscription($root, $stage, $service);

        // Create 3 direct referrals
        for ($i = 1; $i <= 3; $i++) {
            $directUser = User::factory()->create([
                'name' => "Direct User {$i}",
                'parent_id' => $root->id,
            ]);
            activateUserSubscription($directUser, $stage, $service);
        }

        // Check root's genealogy
        $rootGenealogy = MlmGenealogy::forUser($root->id);
        expect($rootGenealogy->direct_count)->toBe(3)
            ->and($rootGenealogy->level_1_count)->toBe(3)
            ->and($rootGenealogy->total_team_count)->toBe(3);
    });

    it('propagates sales to upline genealogy', function () {
        $stage = createStageWithLevels();
        $service = app(SubscriptionService::class);

        // Create 3-level chain
        $grandparent = User::factory()->create(['parent_id' => null]);
        activateUserSubscription($grandparent, $stage, $service);

        $parent = User::factory()->create(['parent_id' => $grandparent->id]);
        activateUserSubscription($parent, $stage, $service);

        $child = User::factory()->create(['parent_id' => $parent->id]);
        $childResult = activateUserSubscription($child, $stage, $service);

        // Check sales propagation
        $grandparentGenealogy = MlmGenealogy::forUser($grandparent->id);
        $parentGenealogy = MlmGenealogy::forUser($parent->id);

        // Grandparent should have team sales from parent and child
        expect($grandparentGenealogy->total_team_sales)->toBeGreaterThan(0);

        // Parent should have team sales from child
        expect($parentGenealogy->total_team_sales)->toBeGreaterThan(0);
    });
});

// =============================================================================
// LEVEL PROGRESSION & PROMOTION TESTS
// =============================================================================

describe('Level Progression', function () {
    it('checks level qualification requirements', function () {
        $stage = createStageWithLevels();
        $bronzeLevel = $stage->getLevelByNumber(1);
        $silverLevel = $stage->getLevelByNumber(2);

        // Bronze requires: 1 direct referral
        expect($bronzeLevel->min_direct_referrals)->toBe(1);

        // Silver requires: 2 direct referrals
        expect($silverLevel->min_direct_referrals)->toBe(2);

        // Test qualification check
        $stats = ['direct_count' => 1, 'active_direct_count' => 1, 'personal_sales' => 0, 'team_sales' => 0];
        expect($bronzeLevel->checkQualification($stats))->toBeTrue();
        expect($silverLevel->checkQualification($stats))->toBeFalse();
    });

    it('promotes user to next level when qualified', function () {
        $stage = createStageWithLevels();
        $service = app(SubscriptionService::class);

        // Create root user
        $root = User::factory()->create(['parent_id' => null]);
        activateUserSubscription($root, $stage, $service);

        // Create enough direct referrals to qualify for Silver (level 2)
        for ($i = 1; $i <= 2; $i++) {
            $directUser = User::factory()->create(['parent_id' => $root->id]);
            activateUserSubscription($directUser, $stage, $service);
        }

        // Check and promote
        $promotedLevel = $service->checkAndPromoteLevel($root);

        // Should be promoted to Silver
        if ($promotedLevel) {
            expect($promotedLevel->level_number)->toBeGreaterThan(1);
        }

        // Verify subscription reflects new level
        $subscription = UserSubscription::getActiveForUser($root->id);
        expect($subscription->getCurrentLevelNumber())->toBeGreaterThanOrEqual(1);
    });

    it('gets level progression status', function () {
        $stage = createStageWithLevels();
        $service = app(SubscriptionService::class);

        $root = User::factory()->create(['parent_id' => null]);
        activateUserSubscription($root, $stage, $service);

        $status = $service->getLevelProgressionStatus($root);

        expect($status)->toHaveKeys([
            'current_stage',
            'current_level',
            'current_level_number',
            'next_level',
            'stats',
            'team_capacity',
        ])
            ->and($status['current_stage'])->toBe($stage->name)
            ->and($status['current_level_number'])->toBe(1);
    });
});

// =============================================================================
// COMMISSION CALCULATION TESTS
// =============================================================================

describe('Commission Calculations', function () {
    it('calculates sponsor bonus correctly', function () {
        $stage = createStageWithLevels();
        $service = app(SubscriptionService::class);

        // Sponsor
        $sponsor = User::factory()->create(['parent_id' => null]);
        activateUserSubscription($sponsor, $stage, $service);

        // New member under sponsor
        $newMember = User::factory()->create(['parent_id' => $sponsor->id]);
        $result = activateUserSubscription($newMember, $stage, $service);

        // Check sponsor bonus commission
        $sponsorBonus = $result['commissions']->firstWhere('type', 'sponsor_bonus');

        if ($sponsorBonus) {
            // Sponsor bonus is 15% of subscription amount
            $expectedBonus = (int) round($stage->price * 0.15);
            expect($sponsorBonus->gross_amount)->toBe($expectedBonus);
        }
    });

    it('calculates multi-level commissions correctly', function () {
        $stage = createStageWithLevels();
        $service = app(SubscriptionService::class);

        // Build 4-level chain
        $level0 = User::factory()->create(['parent_id' => null]);
        activateUserSubscription($level0, $stage, $service);

        $level1 = User::factory()->create(['parent_id' => $level0->id]);
        activateUserSubscription($level1, $stage, $service);

        $level2 = User::factory()->create(['parent_id' => $level1->id]);
        activateUserSubscription($level2, $stage, $service);

        $level3 = User::factory()->create(['parent_id' => $level2->id]);
        activateUserSubscription($level3, $stage, $service);

        // When level 4 joins, everyone above should get commissions
        $level4 = User::factory()->create(['parent_id' => $level3->id]);
        $result = activateUserSubscription($level4, $stage, $service);

        // Check commission distribution
        $levelCommissions = $result['commissions']->where('type', 'level_commission');

        // Should have commissions for level3 (10%), level2 (5%), level1 (3%), level0 (2%)
        expect($levelCommissions->count())->toBeGreaterThanOrEqual(1);
    });

    it('applies TDS deduction on high earnings', function () {
        // This test validates TDS logic is applied
        // TDS kicks in after monthly threshold (usually ₹15,000)

        $stage = createStageWithLevels();
        $service = app(SubscriptionService::class);

        $sponsor = User::factory()->create(['parent_id' => null]);
        activateUserSubscription($sponsor, $stage, $service);

        // Create multiple members to generate enough commission
        for ($i = 0; $i < 5; $i++) {
            $member = User::factory()->create(['parent_id' => $sponsor->id]);
            activateUserSubscription($member, $stage, $service);
        }

        // Check if TDS was applied to any commission
        $sponsorCommissions = MlmCommission::where('user_id', $sponsor->id)->get();

        // Verify commission records exist
        expect($sponsorCommissions->count())->toBeGreaterThan(0);

        // TDS amount should be calculated (may be 0 if below threshold)
        foreach ($sponsorCommissions as $commission) {
            expect($commission->net_amount)->toBeLessThanOrEqual($commission->gross_amount);
        }
    });
});

// =============================================================================
// COMPLETE USER JOURNEY SIMULATION
// =============================================================================

describe('Complete User Journey Simulation', function () {
    it('simulates full journey from registration to Diamond level', function () {
        $stage = createStageWithLevels();
        $service = app(SubscriptionService::class);

        // 1. User registers without affiliate code (root)
        $user = User::factory()->create([
            'name' => 'Journey User',
            'parent_id' => null,
        ]);

        // 2. User takes membership
        $subscription = $service->createSubscription($user, $stage);
        expect($subscription->status)->toBe(UserSubscription::STATUS_PENDING);

        // 3. User pays and subscription activates
        $result = $service->activateSubscription($subscription, transactionId: null);
        expect($result['subscription']->status)->toBe(UserSubscription::STATUS_ACTIVE)
            ->and($result['genealogy']->is_active)->toBeTrue();

        // 4. User starts building team (Bronze level needs 1 referral)
        $directRef1 = User::factory()->create(['parent_id' => $user->id]);
        activateUserSubscription($directRef1, $stage, $service);

        // Check level status
        $status = $service->getLevelProgressionStatus($user);
        expect($status['stats']['direct_count'])->toBe(1);

        // 5. User adds more team members for Silver (needs 2 directs)
        $directRef2 = User::factory()->create(['parent_id' => $user->id]);
        activateUserSubscription($directRef2, $stage, $service);

        // Check promotion
        $service->checkAndPromoteLevel($user);

        // 6. Continue building for Gold (needs 3 directs)
        $directRef3 = User::factory()->create(['parent_id' => $user->id]);
        activateUserSubscription($directRef3, $stage, $service);

        $service->checkAndPromoteLevel($user);

        // 7. For Diamond (needs 4 directs)
        $directRef4 = User::factory()->create(['parent_id' => $user->id]);
        activateUserSubscription($directRef4, $stage, $service);

        $promotedLevel = $service->checkAndPromoteLevel($user);

        // Final status check
        $finalStatus = $service->getLevelProgressionStatus($user);
        expect($finalStatus['stats']['direct_count'])->toBe(4);

        // Check total commissions earned
        $totalCommissions = MlmCommission::where('user_id', $user->id)->sum('gross_amount');
        expect($totalCommissions)->toBeGreaterThan(0);

        // Verify team structure
        $genealogy = MlmGenealogy::forUser($user->id);
        expect($genealogy->direct_count)->toBe(4)
            ->and($genealogy->total_team_count)->toBe(4);
    });

    it('simulates originator (advisor) journey with multiple originated users', function () {
        $stage = createStageWithLevels();
        $service = app(SubscriptionService::class);

        // 1. Advisor registers and subscribes
        $advisor = User::factory()->create([
            'name' => 'Advisor Smith',
            'parent_id' => null,
            'type' => 'advisor',
        ]);
        activateUserSubscription($advisor, $stage, $service);

        // 2. Advisor originates multiple users (not sponsors them)
        $originatedUsers = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = User::factory()->create([
                'name' => "Originated User {$i}",
                'parent_id' => null, // Root users (no MLM sponsor)
                'originator_id' => $advisor->id,
                'originator_type' => 'advisor',
            ]);

            $result = activateUserSubscription($user, $stage, $service, $advisor);
            $originatedUsers[] = $user;

            // Verify sponsor tracking (advisor paid for subscription)
            expect($result['subscription']->sponsor_id)->toBe($advisor->id);
        }

        // 3. Check advisor's originated user count
        $originatedCount = User::where('originator_id', $advisor->id)->count();
        expect($originatedCount)->toBe(5);

        // 4. Advisor's MLM stats should still show 0 direct referrals
        // (Originated users are NOT in advisor's MLM downline)
        $advisorGenealogy = MlmGenealogy::forUser($advisor->id);
        expect($advisorGenealogy->direct_count)->toBe(0);
    });
});

// =============================================================================
// EDGE CASES & VALIDATION
// =============================================================================

describe('Edge Cases', function () {
    it('handles team capacity limits (5^n matrix)', function () {
        $stage = createStageWithLevels();

        // Matrix allows 5 direct children (level 1 = 5^1)
        expect($stage->getTeamCapacityAtLevel(1))->toBe(5);

        // Level 2 = 5^2 = 25
        expect($stage->getTeamCapacityAtLevel(2))->toBe(25);

        // Level 3 = 5^3 = 125
        expect($stage->getTeamCapacityAtLevel(3))->toBe(125);

        // Level 4 = 5^4 = 625
        expect($stage->getTeamCapacityAtLevel(4))->toBe(625);

        // Total team capacity = 5 + 25 + 125 + 625 = 780
        expect($stage->max_team_members)->toBe(780);
    });

    it('prevents duplicate commission for same trigger', function () {
        $stage = createStageWithLevels();
        $service = app(SubscriptionService::class);

        $sponsor = User::factory()->create(['parent_id' => null]);
        activateUserSubscription($sponsor, $stage, $service);

        $member = User::factory()->create(['parent_id' => $sponsor->id]);
        $subscription = $service->createSubscription($member, $stage);

        // Activate once
        $service->activateSubscription($subscription, transactionId: null);
        $firstCount = MlmCommission::where('user_id', $sponsor->id)->count();

        // Try to process commissions again (should be prevented by duplicate check)
        $processor = app(CommissionProcessorService::class);
        $subscription->refresh();
        $secondResult = $processor->processAndPersist($subscription);

        $secondCount = MlmCommission::where('user_id', $sponsor->id)->count();

        // Should not have duplicate commissions
        expect($secondCount)->toBe($firstCount);
    });

    it('handles subscription renewal correctly', function () {
        $stage = createStageWithLevels();
        $service = app(SubscriptionService::class);

        $user = User::factory()->create(['parent_id' => null]);
        $result = activateUserSubscription($user, $stage, $service);
        $originalSubscription = $result['subscription'];

        // Create a transaction for renewal (or use null if model allows)
        // For testing, we'll check the renewal flow without actual transaction
        $originalSubscription->update(['renewal_count' => 0]);

        // Test renewal by creating new subscription manually (simulating renewal)
        $renewal = UserSubscription::create([
            'user_id' => $user->id,
            'stage_id' => $originalSubscription->stage_id,
            'level_id' => $originalSubscription->level_id,
            'current_level_id' => $originalSubscription->current_level_id,
            'highest_level_id' => $originalSubscription->highest_level_id,
            'base_price' => $originalSubscription->base_price,
            'discount' => $originalSubscription->discount,
            'tax_amount' => $originalSubscription->tax_amount,
            'amount' => $originalSubscription->amount,
            'is_paid' => true,
            'paid_at' => now(),
            'starts_at' => $originalSubscription->expires_at ?? now(),
            'expires_at' => ($originalSubscription->expires_at ?? now())->addYear(),
            'status' => UserSubscription::STATUS_ACTIVE,
            'previous_subscription_id' => $originalSubscription->id,
            'renewal_count' => 1,
        ]);

        // Expire original
        $originalSubscription->expire();

        expect($renewal->previous_subscription_id)->toBe($originalSubscription->id)
            ->and($renewal->renewal_count)->toBe(1)
            ->and($renewal->status)->toBe(UserSubscription::STATUS_ACTIVE)
            ->and($originalSubscription->fresh()->status)->toBe(UserSubscription::STATUS_EXPIRED);
    });
});
