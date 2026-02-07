<?php

declare(strict_types=1);

use App\Casts\CommissionStatusCast;
use App\Casts\CommissionTypeCast;
use App\Models\Affiliate\AffiliateCommission;
use App\Models\User;
use App\Services\Trends\CommissionTrendService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(CommissionTrendService::class);
});

describe('CommissionTrendService Earnings Trend', function () {
    it('returns user earnings trend', function () {
        // Create paid commissions
        AffiliateCommission::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 100000, // 1000 Rs
        ]);

        $result = $this->service->getEarningsTrend(
            userId: $this->user->id,
            period: 'year'
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('labels')
            ->and($result['data'])->toHaveKey('datasets')
            ->and($result['data']['summary'])->toHaveKey('total_earnings')
            ->and($result['data']['summary'])->toHaveKey('average_monthly');
    });

    it('only includes paid commissions', function () {
        AffiliateCommission::factory()->create([
            'user_id' => $this->user->id,
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 100000,
        ]);

        AffiliateCommission::factory()->create([
            'user_id' => $this->user->id,
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'status' => CommissionStatusCast::PENDING,
            'net_amount' => 50000,
        ]);

        $result = $this->service->getEarningsTrend(
            userId: $this->user->id,
            period: 'year',
            inRupees: true
        );

        expect($result['data']['summary']['total_earnings'])->toBe(1000.0);
    });

    it('supports custom date range', function () {
        $result = $this->service->getEarningsTrend(
            userId: $this->user->id,
            period: 'custom',
            startDate: now()->subYear()->toDateString(),
            endDate: now()->toDateString()
        );

        expect($result['success'])->toBeTrue()
            ->and($result['meta']['period'])->toBe('custom');
    });
});

describe('CommissionTrendService Earnings By Type', function () {
    it('returns breakdown by commission type', function () {
        AffiliateCommission::factory()->create([
            'user_id' => $this->user->id,
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 100000,
        ]);

        AffiliateCommission::factory()->create([
            'user_id' => $this->user->id,
            'type' => CommissionTypeCast::LEVEL_COMMISSION,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 50000,
        ]);

        $result = $this->service->getEarningsByType(
            userId: $this->user->id,
            period: 'year'
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['labels'])->toBeArray()
            ->and($result['data']['summary']['breakdown'])->toBeArray()
            ->and(count($result['data']['summary']['breakdown']))->toBeGreaterThanOrEqual(2);
    });

    it('excludes reversal type from earnings', function () {
        AffiliateCommission::factory()->create([
            'user_id' => $this->user->id,
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 100000,
        ]);

        AffiliateCommission::factory()->create([
            'user_id' => $this->user->id,
            'type' => CommissionTypeCast::REVERSAL,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 50000,
        ]);

        $result = $this->service->getEarningsByType(
            userId: $this->user->id,
            period: 'year'
        );

        // Should not include reversal in breakdown
        $breakdown = $result['data']['summary']['breakdown'];
        expect(array_key_exists(CommissionTypeCast::REVERSAL->value, $breakdown))->toBeFalse();
    });
});

describe('CommissionTrendService Pending vs Paid', function () {
    it('returns pending vs paid comparison', function () {
        AffiliateCommission::factory()->create([
            'user_id' => $this->user->id,
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 100000,
        ]);

        AffiliateCommission::factory()->create([
            'user_id' => $this->user->id,
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'status' => CommissionStatusCast::PENDING,
            'net_amount' => 50000,
        ]);

        $result = $this->service->getPendingVsPaidTrend(
            userId: $this->user->id,
            period: 'year',
            inRupees: true
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['datasets'])->toHaveCount(2)
            ->and($result['data']['summary'])->toHaveKey('total_paid')
            ->and($result['data']['summary'])->toHaveKey('total_pending');
    });
});

describe('CommissionTrendService Status Distribution', function () {
    it('returns commission status distribution', function () {
        AffiliateCommission::factory()->create([
            'user_id' => $this->user->id,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 100000,
        ]);

        AffiliateCommission::factory()->create([
            'user_id' => $this->user->id,
            'status' => CommissionStatusCast::PENDING,
            'net_amount' => 50000,
        ]);

        $result = $this->service->getStatusDistribution(
            userId: $this->user->id,
            period: 'month'
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['labels'])->toBeArray()
            ->and($result['data']['summary']['breakdown'])->toBeArray();
    });
});

describe('CommissionTrendService Platform Stats (Admin)', function () {
    it('returns platform-wide commission trend', function () {
        // Create commissions for multiple users
        $otherUser = User::factory()->create();

        AffiliateCommission::factory()->create([
            'user_id' => $this->user->id,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 100000,
            'tds_amount' => 10000,
        ]);

        AffiliateCommission::factory()->create([
            'user_id' => $otherUser->id,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 200000,
            'tds_amount' => 20000,
        ]);

        $result = $this->service->getPlatformCommissionTrend(
            period: 'year',
            inRupees: true
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['summary'])->toHaveKey('total_commissions')
            ->and($result['data']['summary'])->toHaveKey('total_tds')
            ->and($result['data']['summary']['total_commissions'])->toBe(3000.0)
            ->and($result['data']['summary']['total_tds'])->toBe(300.0);
    });
});

describe('CommissionTrendService Top Earners', function () {
    it('returns top earners list', function () {
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        AffiliateCommission::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 100000,
        ]);

        AffiliateCommission::factory()->count(3)->create([
            'user_id' => $user2->id,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 100000,
        ]);

        AffiliateCommission::factory()->create([
            'user_id' => $user3->id,
            'status' => CommissionStatusCast::PAID,
            'net_amount' => 50000,
        ]);

        $result = $this->service->getTopEarners(
            period: 'year',
            limit: 10,
            inRupees: true
        );

        expect($result['success'])->toBeTrue()
            ->and($result['data']['labels'])->toHaveCount(3)
            ->and($result['data']['summary']['top_earners'])->toHaveCount(3)
            // Top earner should be user2 with 3000 Rs
            ->and($result['data']['summary']['top_earners'][0]['total_earnings'])->toBe(3000.0);
    });
});
