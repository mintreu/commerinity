<?php

declare(strict_types=1);

namespace Database\Factories\Geo;

use App\Models\Geo\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Geo\Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isoCode2 = fake()->unique()->countryCode();

        return [
            'name' => fake()->country(),
            'iso_code_2' => $isoCode2,
            'iso_code_3' => strtoupper(fake()->lexify('???')),
            'isd_code' => fake()->numberBetween(1, 999),
            'address_format' => '{address_1}, {city}, {state_code} {postal_code}, {country}',
            'postcode_required' => fake()->boolean(80),
            'locale' => fake()->randomElement(['en', 'hi', 'es', 'fr', 'de']),
            'region' => fake()->randomElement(['Asia', 'Europe', 'North America', 'South America', 'Africa', 'Oceania']),
            'timezone' => fake()->timezone(),
            'timezone_diff' => fake()->randomElement(['+00:00', '+05:30', '+01:00', '-05:00', '+08:00']),
            'currency' => fake()->currencyCode(),
            'flag' => null,
            'exchange_rate' => [
                'USD' => fake()->randomFloat(2, 0.01, 150),
                'EUR' => fake()->randomFloat(2, 0.01, 150),
            ],
            'multiplier' => fake()->randomFloat(2, 0.5, 2),
            'is_active' => fake()->boolean(70),
        ];
    }

    /**
     * Indicate that the country is India.
     */
    public function india(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'India',
            'iso_code_2' => 'IN',
            'iso_code_3' => 'IND',
            'isd_code' => 91,
            'postcode_required' => true,
            'locale' => 'en',
            'region' => 'Asia',
            'timezone' => 'Asia/Kolkata',
            'timezone_diff' => '+05:30',
            'currency' => 'INR',
            'flag' => '🇮🇳',
            'exchange_rate' => [
                'USD' => 83.12,
                'EUR' => 90.45,
            ],
            'multiplier' => 1.0,
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the country is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the country is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
