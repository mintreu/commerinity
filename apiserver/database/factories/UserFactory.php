<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Explicitly set defaults to match migration
            'type' => \App\Casts\UserTypeCast::REGULAR->value,
            'status' => \App\Casts\UserStatusCast::DRAFT->value,
            'onboarded' => false,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user has a mobile number verified.
     */
    public function withMobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'mobile' => fake()->e164PhoneNumber(),
            'mobile_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the user is a specific type.
     */
    public function withType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * Indicate that the user has a specific status.
     */
    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Indicate that the user has a parent (Affiliate upline).
     */
    public function withParent($parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Indicate that the user is onboarded.
     */
    public function onboarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarded' => true,
        ]);
    }
}
