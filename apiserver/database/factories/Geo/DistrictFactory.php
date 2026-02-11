<?php

declare(strict_types=1);

namespace Database\Factories\Geo;

use App\Models\Geo\District;
use App\Models\Geo\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Geo\District>
 */
class DistrictFactory extends Factory
{
    protected $model = District::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city(),
            'slug' => fake()->slug(),
            'code' => strtoupper(fake()->bothify('D##')),
            'state_id' => State::factory(),
            'is_active' => true,
        ];
    }

    public function forState(State $state): static
    {
        return $this->state(fn () => [
            'state_id' => $state->id,
        ]);
    }
}
