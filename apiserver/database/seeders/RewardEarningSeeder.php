<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\ConditionMatchingCast;
use App\Casts\VoucherActionTypeCast;
use App\Models\Dashboard\Challenge;
use App\Models\User;
use App\Services\Rewards\RewardEarningService;
use Illuminate\Database\Seeder;

class RewardEarningSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (! $user) {
            return;
        }

        $service = new RewardEarningService;

        // Challenge-based voucher reward
        $challenge = Challenge::first();
        $voucherData = [
            'name' => 'Challenge Winner 10% OFF',
            'description' => 'Reward voucher for completing a challenge.',
            'starts_from' => now(),
            'ends_till' => now()->addDays(30),
            'status' => true,
            'usage_per_customer' => 1,
            'coupon_usage_limit' => 100,
            'times_used' => 0,
            'condition_type' => ConditionMatchingCast::MATCH_ALL->value,
            'conditions' => [],
            'end_other_rules' => false,
            'action_type' => VoucherActionTypeCast::BY_PERCENT,
            'discount_amount' => 10,
            'discount_quantity' => 0,
            'discount_step' => 0,
            'apply_to_shipping' => false,
            'free_shipping' => false,
            'min_cart_value' => 0,
            'min_quantity' => 0,
            'sort_order' => 0,
        ];

        $service->issueVoucher(
            $user,
            $voucherData,
            $challenge ? $challenge->getMorphClass() : 'challenge',
            $challenge?->id,
            now()->addDays(30)
        );

        // Game voucher reward (generic source)
        $gameVoucherData = [
            'name' => 'Game Reward ₹50 OFF',
            'description' => 'Game reward voucher for participation.',
            'starts_from' => now(),
            'ends_till' => now()->addDays(15),
            'status' => true,
            'usage_per_customer' => 1,
            'coupon_usage_limit' => 100,
            'times_used' => 0,
            'condition_type' => ConditionMatchingCast::MATCH_ALL->value,
            'conditions' => [],
            'end_other_rules' => false,
            'action_type' => VoucherActionTypeCast::BY_FIXED,
            'discount_amount' => 5000,
            'discount_quantity' => 0,
            'discount_step' => 0,
            'apply_to_shipping' => false,
            'free_shipping' => false,
            'min_cart_value' => 0,
            'min_quantity' => 0,
            'sort_order' => 0,
        ];

        $service->issueVoucher(
            $user,
            $gameVoucherData,
            'game',
            null,
            now()->addDays(15)
        );
    }
}

