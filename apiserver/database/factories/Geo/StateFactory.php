<?php

declare(strict_types=1);

namespace Database\Factories\Geo;

use App\Models\Geo\Country;
use App\Models\Geo\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Geo\State>
 */
class StateFactory extends Factory
{
    protected $model = State::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->state(),
            'code' => strtoupper(fake()->unique()->lexify('??')),
            'country_id' => Country::factory(),
        ];
    }

    /**
     * Create state for a specific country.
     */
    public function forCountry(Country $country): static
    {
        return $this->state(fn (array $attributes) => [
            'country_id' => $country->id,
        ]);
    }

    /**
     * Create state for India.
     */
    public function indianState(): static
    {
        return $this->state(fn (array $attributes) => [
            'country_id' => Country::where('iso_code_2', 'IN')->first()?->id
                ?? Country::factory()->india()->create()->id,
        ]);
    }
}
