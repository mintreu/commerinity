<?php

namespace Database\Factories\Promotion;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'starts_from' => now()->subDays(rand(1, 10)),
            'ends_till' => now()->addDays(rand(5, 15)),
            'status' => true,
            'usage_per_customer' => rand(1, 5),
            'coupon_usage_limit' => rand(10, 100),
            'times_used' => rand(0, 9),
            'condition_type' => (bool) rand(0, 1),
            'conditions' => ['min_cart_total' => 50], // Example condition
            'end_other_rules' => (bool) rand(0, 1),
            'action_type' => 'fixed_discount', // or 'percentage_discount'
            'discount_amount' => $this->faker->randomFloat(2, 5, 50),
            'discount_quantity' => 1,
            'discount_step' => '1',
            'apply_to_shipping' => false,
            'free_shipping' => false,
            'sort_order' => rand(0, 10),
        ];
    }
}
