<?php

namespace Database\Factories\Lifecycle;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lifecycle\Stage>
 */
class StageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word;
        return [
            'name' => $name,
            'url' => Str::slug($name),
            'price' => $this->faker->numberBetween(100, 1000),
            'status' => true,
        ];
    }
}
