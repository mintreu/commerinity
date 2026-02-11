<?php

declare(strict_types=1);

namespace Database\Factories\Geo;

use App\Models\Geo\Block;
use App\Models\Geo\District;
use App\Models\Geo\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Geo\Block>
 */
class BlockFactory extends Factory
{
    protected $model = Block::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $state = State::query()->inRandomOrder()->first() ?? State::factory()->create();
        $district = District::query()->where('state_id', $state->id)->inRandomOrder()->first()
            ?? District::factory()->forState($state)->create();

        return [
            'name' => fake()->city(),
            'url' => fake()->slug(),
            'district_name' => $district->name,
            'district_id' => $district->id,
            'state_code' => $state->code,
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }

    /**
     * Create block for a specific state.
     */
    public function forState(State $state): static
    {
        return $this->state(function () use ($state): array {
            $district = District::query()->where('state_id', $state->id)->inRandomOrder()->first()
                ?? District::factory()->forState($state)->create();

            return [
                'district_id' => $district->id,
                'district_name' => $district->name,
                'state_code' => $state->code,
            ];
        });
    }

    /**
     * Create block without coordinates.
     */
    public function withoutCoordinates(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    /**
     * Create block with specific coordinates.
     */
    public function withCoordinates(float $latitude, float $longitude): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }
}
