<?php

namespace Database\Factories\Lifecycle;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lifecycle\LevelTask>
 */
class LevelTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'  => $name = fake()->sentence,
            'url'   => Str::slug($name),
            'description'   => fake()->paragraph,
            'score' => fake()->randomElement([3,5,8,2,1,10,-5,-8,-2,-10,7]),
            'min_eligible_score'    => fake()->randomElement([0,20,50,100,500,150,70]),
            'min_progress'  => fake()->randomElement([10,20,50,100,500,150,70]),
        ];
    }
}
