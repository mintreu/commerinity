<?php

declare(strict_types=1);

use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('AffiliateGenealogy Model', function () {

    it('can be created with factory', function () {
        $user = User::factory()->create();
        $genealogy = AffiliateGenealogy::factory()->forUser($user)->create();

        expect($genealogy)->toBeInstanceOf(AffiliateGenealogy::class)
            ->and($genealogy->user_id)->toBe($user->id)
            ->and($genealogy->uuid)->not->toBeNull();
    });

    it('auto-generates uuid on creation', function () {
        $user = User::factory()->create();
        $genealogy = AffiliateGenealogy::factory()->forUser($user)->create();

        expect($genealogy->uuid)->toBeString()
            ->and(strlen($genealogy->uuid))->toBe(36); // UUID format
    });

    it('has default values for counters', function () {
        $user = User::factory()->create();
        $genealogy = AffiliateGenealogy::factory()->forUser($user)->create();

        expect($genealogy->direct_count)->toBe(0)
            ->and($genealogy->level_1_count)->toBe(0)
            ->and($genealogy->level_2_count)->toBe(0)
            ->and($genealogy->level_3_count)->toBe(0)
            ->and($genealogy->level_4_count)->toBe(0)
            ->and($genealogy->total_team_count)->toBe(0);
    });

    it('can be created with team counts', function () {
        $user = User::factory()->create();
        $genealogy = AffiliateGenealogy::factory()
            ->forUser($user)
            ->withTeamCounts(5, 25, 125, 625)
            ->create();

        expect($genealogy->level_1_count)->toBe(5)
            ->and($genealogy->level_2_count)->toBe(25)
            ->and($genealogy->level_3_count)->toBe(125)
            ->and($genealogy->level_4_count)->toBe(625)
            ->and($genealogy->total_team_count)->toBe(780);
    });

    it('can be created with sales volumes', function () {
        $user = User::factory()->create();
        $genealogy = AffiliateGenealogy::factory()
            ->forUser($user)
            ->withSales(100000, 200000, 300000, 400000, 500000)
            ->create();

        expect($genealogy->personal_sales)->toBe(100000)
            ->and($genealogy->level_1_sales)->toBe(200000)
            ->and($genealogy->total_team_sales)->toBe(1400000); // sum of level sales
    });

    it('can set placement parent', function () {
        $parent = User::factory()->create();
        $child = User::factory()->withParent($parent->id)->create();

        $parentGenealogy = AffiliateGenealogy::factory()->forUser($parent)->create();
        $childGenealogy = AffiliateGenealogy::factory()
            ->forUser($child)
            ->withPlacementParent($parent, 1)
            ->atDepth(1)
            ->create();

        expect($childGenealogy->placement_parent_id)->toBe($parent->id)
            ->and($childGenealogy->placement_position)->toBe(1)
            ->and($childGenealogy->depth)->toBe(1);
    });

    it('belongs to a user', function () {
        $user = User::factory()->create();
        $genealogy = AffiliateGenealogy::factory()->forUser($user)->create();

        expect($genealogy->user)->toBeInstanceOf(User::class)
            ->and($genealogy->user->id)->toBe($user->id);
    });

    it('can have placement parent relationship', function () {
        $parent = User::factory()->create();
        $child = User::factory()->create();

        AffiliateGenealogy::factory()->forUser($parent)->create();
        $childGenealogy = AffiliateGenealogy::factory()
            ->forUser($child)
            ->withPlacementParent($parent, 1)
            ->create();

        expect($childGenealogy->placementParent)->toBeInstanceOf(User::class)
            ->and($childGenealogy->placementParent->id)->toBe($parent->id);
    });

    it('can be activated and deactivated', function () {
        $user = User::factory()->create();
        $genealogy = AffiliateGenealogy::factory()
            ->forUser($user)
            ->inactive()
            ->create();

        expect($genealogy->is_active)->toBeFalse();

        $genealogy->activate();

        expect($genealogy->is_active)->toBeTrue()
            ->and($genealogy->activated_at)->not->toBeNull();

        $genealogy->deactivate();

        expect($genealogy->is_active)->toBeFalse();
    });

    it('can add sales and update personal volumes', function () {
        $user = User::factory()->create();
        $genealogy = AffiliateGenealogy::factory()->forUser($user)->create();

        $genealogy->addSales(100000, 100); // 1000 rupees, 100 PV

        expect($genealogy->personal_sales)->toBe(100000)
            ->and($genealogy->personal_pv)->toBe(100);
    });

    it('uses route key uuid', function () {
        $user = User::factory()->create();
        $genealogy = AffiliateGenealogy::factory()->forUser($user)->create();

        expect($genealogy->getRouteKeyName())->toBe('uuid');
    });

    it('can get upline chain', function () {
        // Create parent chain: grandparent -> parent -> user
        $grandparent = User::factory()->create();
        $parent = User::factory()->withParent($grandparent->id)->create();
        $user = User::factory()->withParent($parent->id)->create();

        AffiliateGenealogy::factory()->forUser($grandparent)->atDepth(0)->create();
        AffiliateGenealogy::factory()->forUser($parent)->atDepth(1)->create();
        $genealogy = AffiliateGenealogy::factory()->forUser($user)->atDepth(2)->create();

        $upline = $genealogy->getUpline(4);

        expect($upline)->toHaveCount(2)
            ->and($upline->first()->user_id)->toBe($parent->id)
            ->and($upline->last()->user_id)->toBe($grandparent->id);
    });

    it('limits upline to max levels', function () {
        // Create 5-level chain
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

        $lastGenealogy = AffiliateGenealogy::forUser($users[4]->id);
        $upline = $lastGenealogy->getUpline(2); // Only get 2 levels

        expect($upline)->toHaveCount(2);
    });

    it('can be found by user id', function () {
        $user = User::factory()->create();
        $genealogy = AffiliateGenealogy::factory()->forUser($user)->create();

        $found = AffiliateGenealogy::forUser($user->id);

        expect($found)->not->toBeNull()
            ->and($found->id)->toBe($genealogy->id);
    });

    it('returns null for non-existent user', function () {
        $found = AffiliateGenealogy::forUser(99999);

        expect($found)->toBeNull();
    });

    it('can create for user with depth calculation', function () {
        $parent = User::factory()->create();
        $child = User::factory()->withParent($parent->id)->create();

        AffiliateGenealogy::factory()->forUser($parent)->atDepth(0)->create();
        $childGenealogy = AffiliateGenealogy::createForUser($child->id);

        expect($childGenealogy->depth)->toBe(1)
            ->and($childGenealogy->placement_parent_id)->toBe($parent->id);
    });

    it('scope active works', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        AffiliateGenealogy::factory()->forUser($user1)->create(['is_active' => true]);
        AffiliateGenealogy::factory()->forUser($user2)->create(['is_active' => false]);

        expect(AffiliateGenealogy::active()->count())->toBe(1);
    });
});
