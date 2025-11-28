<?php

namespace Database\Factories\Lifecycle;

use App\Models\Lifecycle\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lifecycle\Level>
 */
class LevelFactory extends Factory
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
            'stage_id' => Stage::factory(),
            'name' => $name,
            'url' => Str::slug($name),
            'team_member_limit' => $this->faker->numberBetween(5, 20),
            'joining_bonus' => $this->faker->numberBetween(1, 10),
            'status' => true,
        ];
    }
}
