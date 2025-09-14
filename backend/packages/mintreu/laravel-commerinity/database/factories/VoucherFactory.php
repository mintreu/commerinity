<?php

namespace Mintreu\LaravelCommerinity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mintreu\LaravelCommerinity\Casts\VoucherActionTypeCast;
use Mintreu\LaravelCommerinity\Models\Voucher;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Mintreu\LaravelCommerinity\Models\Voucher>
 */
class VoucherFactory extends Factory
{

    protected $model = Voucher::class;


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'description' => fake()->sentence,
            'starts_from' => now()->subDays(fake()->numberBetween(5, 15)),
            'ends_till' => now()->addDays(20),
            'status' => fake()->boolean,

            'usage_per_customer' => fake()->numberBetween(2, 3),
            'coupon_usage_limit' => fake()->numberBetween(5, 6),
            'times_used' => 0,
            'condition_type' => 1,
            'end_other_rules' => fake()->boolean,
            'action_type' => fake()->randomElement(array_flip(collect(VoucherActionTypeCast::cases())->values()->toArray())),

            'discount_amount' => fake()->numberBetween(2000, 6000),
            'discount_quantity' => fake()->numberBetween(5, 6),
            'discount_step' => 2,
            'apply_to_shipping' => 0,
            'free_shipping' => 1,
            'sort_order' => 1,
            'conditions' => $this->preSetConditions(),
        ];
    }

    private function preSetConditions(): array
    {
        return [
            [
                'attribute' => 'cart|subTotal',
                'value' => '500',
                'operator' => '>',
            ],
            [
                'attribute' => 'cart|totalQuantity',
                'value' => '2',
                'operator' => '>',
            ],
            [
                'attribute' => 'cart_item|quantity',
                'value' => '2',
                'operator' => '==',
            ],
            [
                'attribute' => 'product|quantity',
                'value' => '1',
                'operator' => '>',
            ],
        ];
    }
}
