<?php

namespace Mintreu\LaravelCategory\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Mintreu\LaravelCategory\Models\Category;


class CategoryFactory extends Factory
{
    protected $model = Category::class;
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
            'is_visible_on_front' => fake()->boolean(99),
            'view_count' => fake()->numberBetween(0, 5000),
            'order' => fake()->optional()->numberBetween(1, 100),
            'meta_data' => ['keywords' => fake()->words(3)],
            'desc' => fake()->paragraph(),
        ];
    }
}
