<?php

declare(strict_types=1);

namespace Database\Factories\Ecommerce;

use App\Models\Ecommerce\FilterGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FilterGroup>
 */
class FilterGroupFactory extends Factory
{
    protected $model = FilterGroup::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
        ];
    }
}
