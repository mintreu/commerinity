<?php

declare(strict_types=1);

namespace Database\Factories\Ecommerce;

use App\Casts\SaleActionTypeCast;
use App\Models\Ecommerce\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'starts_from' => now()->subDay(),
            'ends_till' => now()->addDay(),
            'status' => true,
            'condition_type' => 'match_all',
            'conditions' => [],
            'end_other_rules' => false,
            'action_type' => SaleActionTypeCast::BY_FIXED->value,
            'discount_amount' => 0,
            'sort_order' => 0,
            'target_user_types' => null,
            'target_wholesale_only' => false,
        ];
    }
}
