<?php

namespace Database\Factories;

use App\Casts\AuthStatusCast;
use App\Casts\AuthTypeCast;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mintreu\Toolkit\Casts\GenderCast;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),

            'mobile'            => $this->faker->unique()->numerify('##########'),
            'mobile_verified_at' => now(),

            // Enums → must be cast to string (value)
            'gender' => collect(GenderCast::cases())
                ->mapWithKeys(fn($case) => [$case->value => $case])
                ->keys()
                ->random(),

            'status' => collect(AuthStatusCast::cases())
                ->mapWithKeys(fn($case) => [$case->value => $case])
                ->keys()
                ->random(),

            'type' => collect(AuthTypeCast::cases())
                ->mapWithKeys(fn($case) => [$case->value => $case])
                ->keys()
                ->random(),

//            'referral_code' => Str::uuid(),

            // Realistic dob: between 18 and 60 years ago
            'dob' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'mobile_verified_at' => null,
        ]);
    }


    public function noMobile():static
    {
        return $this->state(fn (array $attributes) => [
            'mobile' => null,
        ]);
    }


}
