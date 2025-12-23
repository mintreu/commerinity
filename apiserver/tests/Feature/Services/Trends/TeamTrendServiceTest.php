<?php

declare(strict_types=1);

use App\Casts\UserTypeCast;
use App\Models\User;
use App\Services\Trends\TeamTrendService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['type' => UserTypeCast::PROMOTER]);
    $this->service = app(TeamTrendService::class);
});

describe('TeamTrendService Direct Referral Trend', function () {
    it('returns direct referral growth trend', function () {
        // Create direct referrals
        User::factory()->count(3)->create([
            'parent_id' => $this->user->id,
        ]);

        $result = $this->service->getDirectReferralTrend(
            userId: $this->user->id,
            period: 'year'
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('labels')
            ->and($result['data'])->toHaveKey('datasets')
            ->and($result['data']['summary'])->toHaveKey('new_referrals')
            ->and($result['data']['summary'])->toHaveKey('total_direct')
            ->and($result['data']['summary'])->toHaveKey('active_direct')
            ->and($result['data']['summary']['total_direct'])->toBe(3);
    });

    it('supports different periods', function () {
        $periods = ['week', 'month', 'quarter', 'year'];

        foreach ($periods as $period) {
            $result = $this->service->getDirectReferralTrend(
                userId: $this->user->id,
                period: $period
            );

            expect($result['success'])->toBeTrue()
                ->and($result['meta']['period'])->toBe($period);
        }
    });

    it('distinguishes active vs inactive referrals', function () {
        // Create 2 active members
        User::factory()->count(2)->create([
            'parent_id' => $this->user->id,
            'type' => UserTypeCast::MEMBER,
        ]);

        // Create 3 inactive (regular) users
        User::factory()->count(3)->create([
            'parent_id' => $this->user->id,
            'type' => UserTypeCast::REGULAR,
        ]);

        $result = $this->service->getDirectReferralTrend(
            userId: $this->user->id,
            period: 'year'
        );

        expect($result['data']['summary']['total_direct'])->toBe(5)
            ->and($result['data']['summary']['active_direct'])->toBe(2)
            ->and($result['data']['summary']['inactive_direct'])->toBe(3);
    });
});

describe('TeamTrendService Active Inactive Trend', function () {
    it('returns active vs inactive trend over time', function () {
        User::factory()->count(2)->create([
            'parent_id' => $this->user->id,
            'type' => UserTypeCast::MEMBER,
        ]);

        User::factory()->count(3)->create([
            'parent_id' => $this->user->id,
            'type' => UserTypeCast::REGULAR,
        ]);

        $result = $this->service->getActiveInactiveTrend(
            userId: $this->user->id,
            period: 'year'
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['datasets'])->toHaveCount(2)
            ->and($result['data']['summary'])->toHaveKey('new_active')
            ->and($result['data']['summary'])->toHaveKey('new_inactive');
    });
});

describe('TeamTrendService Team By Type', function () {
    it('returns team distribution by user type', function () {
        User::factory()->create([
            'parent_id' => $this->user->id,
            'type' => UserTypeCast::MEMBER,
        ]);

        User::factory()->create([
            'parent_id' => $this->user->id,
            'type' => UserTypeCast::PROMOTER,
        ]);

        User::factory()->count(2)->create([
            'parent_id' => $this->user->id,
            'type' => UserTypeCast::REGULAR,
        ]);

        $result = $this->service->getTeamByType(
            userId: $this->user->id,
            period: 'year'
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['labels'])->toBeArray()
            ->and($result['data']['summary']['breakdown'])->toBeArray()
            ->and($result['data']['summary']['total'])->toBe(4);
    });
});

describe('TeamTrendService Originated Users (Advisor)', function () {
    it('returns originated users trend for advisors', function () {
        $advisor = User::factory()->create(['type' => UserTypeCast::ADVISOR]);

        // Create originated users
        User::factory()->count(5)->create([
            'originator_id' => $advisor->id,
            'originator_type' => User::class,
        ]);

        $result = $this->service->getOriginatedUsersTrend(
            originatorId: $advisor->id,
            period: 'year'
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['summary'])->toHaveKey('new_originated')
            ->and($result['data']['summary'])->toHaveKey('total_originated')
            ->and($result['data']['summary'])->toHaveKey('active_originated')
            ->and($result['data']['summary'])->toHaveKey('conversion_rate')
            ->and($result['data']['summary']['total_originated'])->toBe(5);
    });

    it('calculates conversion rate correctly', function () {
        $advisor = User::factory()->create(['type' => UserTypeCast::ADVISOR]);

        // 2 active members
        User::factory()->count(2)->create([
            'originator_id' => $advisor->id,
            'originator_type' => User::class,
            'type' => UserTypeCast::MEMBER,
        ]);

        // 3 inactive (regular)
        User::factory()->count(3)->create([
            'originator_id' => $advisor->id,
            'originator_type' => User::class,
            'type' => UserTypeCast::REGULAR,
        ]);

        $result = $this->service->getOriginatedUsersTrend(
            originatorId: $advisor->id,
            period: 'year'
        );

        expect($result['data']['summary']['total_originated'])->toBe(5)
            ->and($result['data']['summary']['active_originated'])->toBe(2)
            ->and($result['data']['summary']['conversion_rate'])->toBe(40.0);
    });
});

describe('TeamTrendService Team Performance', function () {
    it('returns team performance summary', function () {
        // Create referrals this month
        User::factory()->count(2)->create([
            'parent_id' => $this->user->id,
            'type' => UserTypeCast::MEMBER,
        ]);

        // Create referrals last month
        User::factory()->count(3)->create([
            'parent_id' => $this->user->id,
            'type' => UserTypeCast::REGULAR,
            'created_at' => now()->subMonth(),
        ]);

        $result = $this->service->getTeamPerformance($this->user->id);

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('direct_referrals')
            ->and($result['data'])->toHaveKey('active_referrals')
            ->and($result['data'])->toHaveKey('activation_rate')
            ->and($result['data'])->toHaveKey('this_month')
            ->and($result['data'])->toHaveKey('last_month')
            ->and($result['data'])->toHaveKey('growth_rate')
            ->and($result['data']['direct_referrals'])->toBe(5);
    });
});
