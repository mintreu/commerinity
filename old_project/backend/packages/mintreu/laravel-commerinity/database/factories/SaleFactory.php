<?php

namespace Mintreu\LaravelCommerinity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mintreu\LaravelCommerinity\Models\Sale;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;
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
            'starts_from' => now(),
            'ends_till' => now()->addDays(20),
            'status' => fake()->boolean,
            'condition_type' => 1,
            'end_other_rules' => fake()->boolean,
            'action_type' => fake()->randomElement(['by_percent', 'by_fixed']),
            'discount_amount' => fake()->numberBetween(2000, 6000),
            'sort_order' => 1,
            'conditions' => $this->preSetConditions(),
        ];
    }

    private function preSetConditions(): array
    {
        return [
            [
                'attribute' => 'product|price',
                'value' => fake()->numberBetween(100, 300),
                'operator' => '>',
            ],
            [
                'attribute' => 'filter|color',
                'value' => fake()->numberBetween(1, 8),
                'operator' => '>',
            ],
        ];
    }
}
