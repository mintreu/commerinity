<?php

declare(strict_types=1);

use App\Casts\CommissionTypeCast;
use App\Casts\OrderStatusCast;
use App\Casts\UserTypeCast;
use App\Models\Address;
use App\Models\Affiliate\AffiliateCommission;
use App\Models\Affiliate\AffiliateGenealogy;
use App\Models\Ecommerce\Order;
use App\Models\Ecommerce\OrderItem;
use App\Models\Ecommerce\Product;
use App\Models\Membership\Stage;
use App\Models\User;
use App\Services\Ecommerce\OrderService;
use App\Services\Membership\SubscriptionService;
use Database\Factories\Membership\StageFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    Config::set('affiliate.member_commissions.enabled', true);
    Config::set('affiliate.member_commissions.sponsor_bonus.enabled', true);
    Config::set('affiliate.member_commissions.level_commission.enabled', true);
    Config::set('affiliate.matrix.depth', 4);
    Config::set('affiliate.originator_commissions.enabled', true);
    Config::set('affiliate.tds.enabled', false);
    Config::set('affiliate.admin_fee.enabled', false);
    Config::set('queue.default', 'sync');
});

test('affiliate tree processes sponsor and level commissions end to end', function () {
    StageFactory::resetCounter();
    $stage = StageFactory::new()->withLevels()->create();

    $subscriptionService = app(SubscriptionService::class);

    $root = createAffiliateUser(null, $stage, $subscriptionService, 'root');
    $descendants = [];
    $levelCounts = [];

    buildAffiliateTree($root, $stage, $subscriptionService, 1, 2, $descendants, $levelCounts);

    $levelOne = $levelCounts[1] ?? 0;
    $levelTwo = $levelCounts[2] ?? 0;
    $totalDescendants = count($descendants);

    expect($levelOne)->toBe(5)
        ->and($levelTwo)->toBe(25)
        ->and($totalDescendants)->toBe(30);

    $rootGenealogy = AffiliateGenealogy::forUser($root->id);
    expect($rootGenealogy)->not->toBeNull();
    expect($rootGenealogy->level_1_count)->toBe($levelOne);
    expect($rootGenealogy->level_2_count)->toBe($levelTwo);
    expect($rootGenealogy->total_team_count)->toBe($levelOne + $levelTwo);

    $stagePrice = $stage->price;
    $levelOneRate = $stage->getCommissionRate(1);
    $levelTwoRate = $stage->getCommissionRate(2);
    $sponsorBonusPer = $stage->getSponsorBonusAmount($stagePrice);

    $expectedLevel1Commission = percentOf($stagePrice, $levelOneRate);
    $expectedLevel2Commission = percentOf($stagePrice, $levelTwoRate);

    $expectedRootSponsorTotal = $sponsorBonusPer * $levelOne;
    $expectedRootLevelTotal = ($expectedLevel1Commission * $levelOne)
        + ($expectedLevel2Commission * $levelTwo);

    $rootSponsorBonus = (int) AffiliateCommission::query()
        ->where('user_id', $root->id)
        ->where('type', CommissionTypeCast::SPONSOR_BONUS->value)
        ->sum('gross_amount');

    $rootLevelCommission = (int) AffiliateCommission::query()
        ->where('user_id', $root->id)
        ->where('type', CommissionTypeCast::LEVEL_COMMISSION->value)
        ->sum('gross_amount');

    expect($rootSponsorBonus)->toBe($expectedRootSponsorTotal)
        ->and($rootLevelCommission)->toBe($expectedRootLevelTotal);

    $levelCommissionCount = AffiliateCommission::query()
        ->where('type', CommissionTypeCast::LEVEL_COMMISSION->value)
        ->count();
    $sponsorCommissionCount = AffiliateCommission::query()
        ->where('type', CommissionTypeCast::SPONSOR_BONUS->value)
        ->count();

    expect($sponsorCommissionCount)->toBe($totalDescendants)
        ->and($levelCommissionCount)->toBe(($levelOne * 1) + ($levelTwo * 2));
});

test('completed order triggers affiliate purchase commissions', function () {
    StageFactory::resetCounter();
    $stage = StageFactory::new()->withLevels()->create();
    $subscriptionService = app(SubscriptionService::class);

    $root = createAffiliateUser(null, $stage, $subscriptionService, 'root');
    $sponsor = createAffiliateUser($root, $stage, $subscriptionService, 'sponsor');
    $customer = createAffiliateUser($sponsor, $stage, $subscriptionService, 'customer');

    $address = Address::factory()->forUser($customer)->default()->create();

    $product = Product::factory()->create([
        'bv' => 10000,
        'pv' => 5000,
        'is_returnable' => false,
        'return_days' => 0,
    ]);

    $tierBeforeSponsorBonus = AffiliateCommission::query()
        ->where('user_id', $sponsor->id)
        ->where('type', CommissionTypeCast::SPONSOR_BONUS->value)
        ->sum('gross_amount');
    $tierBeforeSponsorLevel = AffiliateCommission::query()
        ->where('user_id', $sponsor->id)
        ->where('type', CommissionTypeCast::LEVEL_COMMISSION->value)
        ->where('level', 1)
        ->sum('gross_amount');
    $tierBeforeRootLevel = AffiliateCommission::query()
        ->where('user_id', $root->id)
        ->where('type', CommissionTypeCast::LEVEL_COMMISSION->value)
        ->where('level', 2)
        ->sum('gross_amount');
    $beforeLevelCommissionCount = AffiliateCommission::query()
        ->where('type', CommissionTypeCast::LEVEL_COMMISSION->value)
        ->count();

    $order = Order::create([
        'customerable_type' => User::class,
        'customerable_id' => $customer->id,
        'status' => OrderStatusCast::PENDING->value,
        'subtotal' => 100000,
        'shipping_cost' => 0,
        'tax' => 0,
        'discount' => 0,
        'total' => 100000,
        'total_bv' => 10000,
        'total_pv' => 5000,
        'total_reward_points' => 0,
        'quantity' => 1,
        'shipping_address_id' => $address->id,
        'billing_address_id' => $address->id,
        'expire_at' => now()->addMinutes(30),
    ]);

    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'stock_id' => null,
        'quantity' => 1,
        'unit_price' => 100000,
        'total_price' => 100000,
        'bv' => 10000,
        'pv' => 5000,
        'reward_points' => 0,
    ]);

    $orderItem->update([
        'product_name' => $product->name,
        'product_sku' => $product->sku,
    ]);

    $orderService = app(OrderService::class);

    expect($orderService->markAsDelivered($order))->toBeTrue();
    expect($orderService->markAsCompleted($order))->toBeTrue();

    $order->refresh();
    expect($order->commission_processed)->toBeTrue();
    expect($order->total_bv)->toBe(10000);

    $orderBv = $order->total_bv;

    $afterSponsorBonus = (int) AffiliateCommission::query()
        ->where('user_id', $sponsor->id)
        ->where('type', CommissionTypeCast::SPONSOR_BONUS->value)
        ->sum('gross_amount');

    $afterSponsorLevel = (int) AffiliateCommission::query()
        ->where('user_id', $sponsor->id)
        ->where('type', CommissionTypeCast::LEVEL_COMMISSION->value)
        ->where('level', 1)
        ->sum('gross_amount');

    $afterRootLevel = (int) AffiliateCommission::query()
        ->where('user_id', $root->id)
        ->where('type', CommissionTypeCast::LEVEL_COMMISSION->value)
        ->where('level', 2)
        ->sum('gross_amount');

    $afterLevelCommissionCount = AffiliateCommission::query()
        ->where('type', CommissionTypeCast::LEVEL_COMMISSION->value)
        ->count();

    $sponsorBonus = $afterSponsorBonus - $tierBeforeSponsorBonus;
    $sponsorLevel = $afterSponsorLevel - $tierBeforeSponsorLevel;
    $rootLevel = $afterRootLevel - $tierBeforeRootLevel;
    $levelCommissionCount = $afterLevelCommissionCount - $beforeLevelCommissionCount;

    expect($sponsorBonus)->toBe(0)
        ->and($sponsorLevel)->toBeGreaterThan(0)
        ->and($rootLevel)->toBeGreaterThan(0)
        ->and($levelCommissionCount)->toBe(2);
});

function buildAffiliateTree(
    User $parent,
    Stage $stage,
    SubscriptionService $service,
    int $currentDepth,
    int $maxDepth,
    array &$collected,
    array &$levelCounts
): void {
    if ($currentDepth > $maxDepth) {
        return;
    }

    $width = $stage->matrix_width;

    for ($i = 0; $i < $width; $i++) {
        $child = createAffiliateUser($parent, $stage, $service);
        $levelCounts[$currentDepth] = ($levelCounts[$currentDepth] ?? 0) + 1;
        $collected[] = $child;

        buildAffiliateTree($child, $stage, $service, $currentDepth + 1, $maxDepth, $collected, $levelCounts);
    }
}

function createAffiliateUser(
    ?User $parent,
    Stage $stage,
    SubscriptionService $service,
    ?string $emailPrefix = null
): User {
    $email = ($emailPrefix ? "{$emailPrefix}." : '') . Str::uuid()->toString() . '@affiliate.test';

    $user = User::factory()->create([
        'parent_id' => $parent?->id,
        'type' => UserTypeCast::REGULAR->value,
        'email' => $email,
    ]);

    $subscription = $parent
        ? $service->createSponsoredSubscription($user, $stage, $parent)
        : $service->createSubscription($user, $stage);

    $service->activateSubscription($subscription);

    return $user;
}

function percentOf(int $amount, float $percent): int
{
    return (int) round($amount * ($percent / 100));
}
