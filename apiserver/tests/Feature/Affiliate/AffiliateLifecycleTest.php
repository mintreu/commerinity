<?php

declare(strict_types=1);

use App\Casts\CommissionTypeCast;
use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\User;
use App\Services\UserServices\UserAffiliateService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Affiliate Lifecycle Test
|--------------------------------------------------------------------------
|
| Comprehensive tests for the full Affiliate lifecycle:
| 1. User joins under sponsor (finds available slot via BFS)
| 2. Slot filling with 5x4 matrix (5 children max, 4 levels deep)
| 3. Commission distribution to uplines
| 4. Originator (agent/advisor) commission calculation
| 5. Team counter updates
| 6. Network growth tracking
|
*/

describe('Affiliate User Placement', function () {

    it('places user directly under sponsor when slot available', function () {
        $affiliateService = app(UserAffiliateService::class);

        // Create sponsor with 3 children (has 2 slots available)
        $sponsor = User::factory()->create();
        AffiliateGenealogy::factory()->forUser($sponsor)->create();

        // Create 3 existing children
        for ($i = 0; $i < 3; $i++) {
            $child = User::factory()->withParent($sponsor->id)->create();
            AffiliateGenealogy::factory()->forUser($child)->atDepth(1)->create();
        }

        // Find slot for new user
        $slot = $affiliateService->findAvailableSlot($sponsor);

        expect($slot)->not->toBeNull()
            ->and($slot['parent_id'])->toBe($sponsor->id)
            ->and($slot['position'])->toBe(4);
    });

    it('finds slot in descendants when sponsor is full (BFS)', function () {
        $affiliateService = app(UserAffiliateService::class);

        // Create sponsor with 5 children (full)
        $sponsor = User::factory()->create();
        AffiliateGenealogy::factory()->forUser($sponsor)->create();

        $firstChild = null;
        for ($i = 0; $i < 5; $i++) {
            $child = User::factory()->withParent($sponsor->id)->create();
            AffiliateGenealogy::factory()->forUser($child)->atDepth(1)->create();
            if ($i === 0) {
                $firstChild = $child;
            }
        }

        // Find slot - should be under first child
        $slot = $affiliateService->findAvailableSlot($sponsor);

        expect($slot)->not->toBeNull()
            ->and($slot['parent_id'])->toBe($firstChild->id)
            ->and($slot['position'])->toBe(1);
    });

    it('places user in tree with correct depth', function () {
        $affiliateService = app(UserAffiliateService::class);

        // Create root user
        $root = User::factory()->create();
        AffiliateGenealogy::factory()->forUser($root)->atDepth(0)->create();

        // Create new user under root
        $newUser = User::factory()->create();
        $genealogy = $affiliateService->placeUser($newUser, $root);

        $newUser->refresh();

        expect($newUser->parent_id)->toBe($root->id)
            ->and($genealogy->depth)->toBe(1)
            ->and($genealogy->placement_parent_id)->toBe($root->id)
            ->and($genealogy->placement_position)->toBe(1);
    });

    it('can accept up to 5 direct children per user', function () {
        $affiliateService = app(UserAffiliateService::class);

        $parent = User::factory()->create();
        AffiliateGenealogy::factory()->forUser($parent)->create();

        // Should accept first 5 children
        for ($i = 0; $i < 5; $i++) {
            expect($affiliateService->canAcceptChildren($parent->id))->toBeTrue();

            $child = User::factory()->withParent($parent->id)->create();
            AffiliateGenealogy::factory()->forUser($child)->atDepth(1)->create();
        }

        // 6th should go to descendant
        expect($affiliateService->canAcceptChildren($parent->id))->toBeFalse();
    });

    it('builds 5x4 matrix correctly (max 780 members)', function () {
        $affiliateService = app(UserAffiliateService::class);

        // Create root
        $root = User::factory()->create();
        AffiliateGenealogy::factory()
            ->forUser($root)
            ->withTeamCounts(5, 25, 125, 625)
            ->create();

        // Total: 5 + 25 + 125 + 625 = 780
        $stats = $affiliateService->getTeamStats($root->id);

        expect($stats['level_1_count'])->toBe(5)
            ->and($stats['level_2_count'])->toBe(25)
            ->and($stats['level_3_count'])->toBe(125)
            ->and($stats['level_4_count'])->toBe(625)
            ->and($stats['total_team_count'])->toBe(780);
    });
});

describe('Affiliate Commission Distribution', function () {

    beforeEach(function () {
        // Set up config for commission rates
        config([
            'affiliate.commission_rates' => [
                1 => 10.0, // Level 1: 10%
                2 => 5.0,  // Level 2: 5%
                3 => 3.0,  // Level 3: 3%
                4 => 2.0,  // Level 4: 2%
            ],
            'affiliate.originator_commission_rate' => 5.0,
        ]);
    });

    it('distributes level commissions to uplines', function () {
        $affiliateService = app(UserAffiliateService::class);

        // Create 4-level chain: root -> l1 -> l2 -> l3 -> purchaser
        $root = User::factory()->create();
        $rootGen = AffiliateGenealogy::factory()->forUser($root)->atDepth(0)->create();

        $l1 = User::factory()->withParent($root->id)->create();
        $l1Gen = AffiliateGenealogy::factory()->forUser($l1)->atDepth(1)->create();

        $l2 = User::factory()->withParent($l1->id)->create();
        $l2Gen = AffiliateGenealogy::factory()->forUser($l2)->atDepth(2)->create();

        $l3 = User::factory()->withParent($l2->id)->create();
        $l3Gen = AffiliateGenealogy::factory()->forUser($l3)->atDepth(3)->create();

        $purchaser = User::factory()->withParent($l3->id)->create();
        AffiliateGenealogy::factory()->forUser($purchaser)->atDepth(4)->create();

        // Create a mock commissionable model
        $mockCommissionable = new class
        {
            public function getKey(): int
            {
                return 1;
            }
        };

        // Distribute commissions for 100000 paisa (1000 rupees) purchase
        $commissions = $affiliateService->distributeCommissions($purchaser->id, 100000, $mockCommissionable);

        expect($commissions)->toHaveCount(4);

        // Verify each level's commission
        $l3Commission = $commissions->firstWhere('user_id', $l3->id);
        $l2Commission = $commissions->firstWhere('user_id', $l2->id);
        $l1Commission = $commissions->firstWhere('user_id', $l1->id);
        $rootCommission = $commissions->firstWhere('user_id', $root->id);

        expect($l3Commission->level)->toBe(1)
            ->and($l3Commission->gross_amount)->toBe(10000) // 10%
            ->and($l2Commission->level)->toBe(2)
            ->and($l2Commission->gross_amount)->toBe(5000)  // 5%
            ->and($l1Commission->level)->toBe(3)
            ->and($l1Commission->gross_amount)->toBe(3000)  // 3%
            ->and($rootCommission->level)->toBe(4)
            ->and($rootCommission->gross_amount)->toBe(2000); // 2%
    });

    it('calculates originator commission for agent/advisor', function () {
        $affiliateService = app(UserAffiliateService::class);

        // Create an advisor (originator) - uses valid UserTypeCast value
        $agent = User::factory()->withType(\App\Casts\UserTypeCast::ADVISOR->value)->create();

        // Create user recruited by agent
        $user = User::factory()->create();
        $user->update([
            'originator_type' => User::class,
            'originator_id' => $agent->id,
        ]);
        $user->refresh();

        // Create mock commissionable
        $mockCommissionable = new class
        {
            public function getKey(): int
            {
                return 1;
            }
        };

        // Calculate originator commission
        $commission = $affiliateService->calculateOriginatorCommission($user, 100000, $mockCommissionable);

        expect($commission)->not->toBeNull()
            ->and($commission->user_id)->toBe($agent->id)
            ->and($commission->from_user_id)->toBe($user->id)
            ->and($commission->type->getValue())->toBe(CommissionTypeCast::SPONSOR_BONUS)
            ->and($commission->gross_amount)->toBe(5000); // 5% of 100000
    });

    it('skips commission for inactive uplines', function () {
        $affiliateService = app(UserAffiliateService::class);

        // Create active root
        $root = User::factory()->create();
        AffiliateGenealogy::factory()->forUser($root)->atDepth(0)->create(['is_active' => true]);

        // Create inactive middle user
        $middle = User::factory()->withParent($root->id)->create();
        AffiliateGenealogy::factory()->forUser($middle)->atDepth(1)->create(['is_active' => false]);

        // Create purchaser
        $purchaser = User::factory()->withParent($middle->id)->create();
        AffiliateGenealogy::factory()->forUser($purchaser)->atDepth(2)->create();

        $mockCommissionable = new class
        {
            public function getKey(): int
            {
                return 1;
            }
        };

        $commissions = $affiliateService->distributeCommissions($purchaser->id, 100000, $mockCommissionable);

        // Only root should get commission (level 2), middle is inactive
        expect($commissions)->toHaveCount(1)
            ->and($commissions->first()->user_id)->toBe($root->id);
    });
});

describe('Affiliate Team Counters', function () {

    it('increments upline counters when user joins', function () {
        $affiliateService = app(UserAffiliateService::class);

        // Create 2-level chain
        $root = User::factory()->create();
        $rootGen = AffiliateGenealogy::factory()->forUser($root)->atDepth(0)->create();

        $l1 = User::factory()->withParent($root->id)->create();
        AffiliateGenealogy::factory()->forUser($l1)->atDepth(1)->create();

        // Add new user
        $newUser = User::factory()->withParent($l1->id)->create();
        $affiliateService->placeUser($newUser, $l1);

        // Check root's counters
        $rootGen->refresh();

        expect($rootGen->level_2_count)->toBe(1)
            ->and($rootGen->total_team_count)->toBe(1);
    });

    it('tracks direct and team counts separately', function () {
        $affiliateService = app(UserAffiliateService::class);

        $root = User::factory()->create();
        $rootGen = AffiliateGenealogy::factory()->forUser($root)->atDepth(0)->create();

        // Add 3 direct children
        for ($i = 0; $i < 3; $i++) {
            $child = User::factory()->create();
            $affiliateService->placeUser($child, $root);
        }

        $rootGen->refresh();

        expect($rootGen->direct_count)->toBe(3)
            ->and($rootGen->level_1_count)->toBe(3);
    });
});

describe('Affiliate Upline Queries (Optimized)', function () {

    it('fetches upline with levels using single query', function () {
        $affiliateService = app(UserAffiliateService::class);

        // Create 4-level chain
        $users = [];
        $prevUser = null;

        for ($i = 0; $i < 5; $i++) {
            $user = User::factory()
                ->when($prevUser, fn ($factory) => $factory->withParent($prevUser->id))
                ->create();
            AffiliateGenealogy::factory()->forUser($user)->atDepth($i)->create();
            $users[] = $user;
            $prevUser = $user;
        }

        // Get uplines for last user
        $uplines = $affiliateService->getUplineWithLevels($users[4]->id, 4);

        expect($uplines)->toHaveCount(4)
            ->and($uplines[1]->id)->toBe($users[3]->id) // Level 1 parent
            ->and($uplines[2]->id)->toBe($users[2]->id) // Level 2
            ->and($uplines[3]->id)->toBe($users[1]->id) // Level 3
            ->and($uplines[4]->id)->toBe($users[0]->id); // Level 4
    });

    it('gets children counts for multiple users efficiently', function () {
        $affiliateService = app(UserAffiliateService::class);

        // Create parent users
        $parents = User::factory()->count(3)->create();

        // Add children to each
        User::factory()->withParent($parents[0]->id)->count(2)->create();
        User::factory()->withParent($parents[1]->id)->count(5)->create();
        User::factory()->withParent($parents[2]->id)->count(3)->create();

        // Get counts in single query
        $counts = $affiliateService->getChildrenCountsForUsers($parents->pluck('id')->toArray());

        expect($counts[$parents[0]->id])->toBe(2)
            ->and($counts[$parents[1]->id])->toBe(5)
            ->and($counts[$parents[2]->id])->toBe(3);
    });
});

describe('Affiliate Network Scenarios', function () {

    it('handles spillover when sponsor slot is full', function () {
        $affiliateService = app(UserAffiliateService::class);

        // Create sponsor with 5 children (full)
        $sponsor = User::factory()->create();
        AffiliateGenealogy::factory()->forUser($sponsor)->atDepth(0)->create();

        $children = [];
        for ($i = 0; $i < 5; $i++) {
            $child = User::factory()->withParent($sponsor->id)->create();
            AffiliateGenealogy::factory()->forUser($child)->atDepth(1)->create();
            $children[] = $child;
        }

        // New user should spillover to first child
        $newUser = User::factory()->create();
        $genealogy = $affiliateService->placeUser($newUser, $sponsor);

        $newUser->refresh();

        // User's parent_id should still be sponsor (referral relationship)
        // But placement should be under first available descendant
        expect($newUser->parent_id)->toBe($sponsor->id)
            ->and($genealogy->placement_parent_id)->toBe($children[0]->id);
    });

    it('maintains referral relationship separate from matrix placement', function () {
        $affiliateService = app(UserAffiliateService::class);

        // Scenario: User A sponsors User B, but A is full
        // B gets placed under A's child C, but B's parent_id remains A

        $userA = User::factory()->create();
        AffiliateGenealogy::factory()->forUser($userA)->atDepth(0)->create();

        // Fill A with 5 children
        $firstChild = null;
        for ($i = 0; $i < 5; $i++) {
            $child = User::factory()->withParent($userA->id)->create();
            AffiliateGenealogy::factory()->forUser($child)->atDepth(1)->create();
            if ($i === 0) {
                $firstChild = $child;
            }
        }

        // B joins under A's referral
        $userB = User::factory()->create();
        $bGenealogy = $affiliateService->placeUser($userB, $userA);

        $userB->refresh();

        // Referral (parent_id) = A, Placement = first child
        expect($userB->parent_id)->toBe($userA->id)
            ->and($bGenealogy->placement_parent_id)->toBe($firstChild->id);
    });

    it('handles user joining as root (no sponsor)', function () {
        $affiliateService = app(UserAffiliateService::class);

        $user = User::factory()->create();
        $genealogy = $affiliateService->placeUser($user, null, null);

        expect($user->parent_id)->toBeNull()
            ->and($genealogy->depth)->toBe(0)
            ->and($genealogy->placement_parent_id)->toBeNull();
    });
});
