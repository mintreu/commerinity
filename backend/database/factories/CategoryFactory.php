<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $name =  fake()->words(2, true),
            'url' => Str::slug($name),
            'status' => fake()->boolean(90),
            'is_visible_on_front' => fake()->boolean(96),
            'view_count' => fake()->numberBetween(0, 5000),
            'order' => fake()->optional()->numberBetween(1, 100),
            'meta_data' => ['keywords' => fake()->words(3)],
            'desc' => fake()->paragraph(),
        ];
    }
}
