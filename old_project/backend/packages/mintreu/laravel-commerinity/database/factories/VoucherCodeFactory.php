<?php

namespace Mintreu\LaravelCommerinity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mintreu\LaravelCommerinity\Models\VoucherCode;

/**
 * @extends Factory<VoucherCode>
 */
class VoucherCodeFactory extends Factory
{

    protected $model = VoucherCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->word,
            'coupon_usage_limit' => fake()->numberBetween(0, 100),
            'usage_per_customer' => fake()->numberBetween(0, 5),
            'times_used' => 0,
            'type' => fake()->numberBetween(0, 2),
            'starts_from' => now(),
            'ends_till' => now()->addDays(21),
        ];
    }
}
