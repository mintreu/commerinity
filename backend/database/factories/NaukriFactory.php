<?php

namespace Database\Factories;

use App\Casts\NaukriEmploymentTypeCast;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Naukri>
 */
class NaukriFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'name' => $jobTitle = fake()->jobTitle().fake()->word(),
            'url' =>  Str::slug($jobTitle),
            'description' => fake()->paragraph(2),
            'role' => fake()->words(2, true),
            'location' => $this->faker->city(),
            'employment_type' => $this->faker->randomElement(
                collect(NaukriEmploymentTypeCast::cases())
                    ->map(fn($case) => $case->value)
                    ->toArray()
            ),
            'vacancy' => $this->faker->numberBetween(1, 100),
            'open_date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'close_date' => $this->faker->dateTimeBetween('now', '+60 days')->format('Y-m-d'),
            'is_payable' => $this->faker->boolean(60),
            'fees' => $this->faker->optional($weight = 0.6)->randomFloat(2, 199, 4999),
            'status' => $this->faker->randomElement(
                collect(PublishableStatusCast::cases())
                    ->map(fn($case) => $case->value)
                    ->toArray()
            ),
        ];
    }
}
