<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Casts\AdminStatusCast;
use App\Casts\AdminTypeCast;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Admin>
 */
final class AdminFactory extends Factory
{
    protected $model = Admin::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'mobile' => fake()->unique()->numerify('##########'),
            'password' => Hash::make('password'),
            'type' => AdminTypeCast::Executive,
            'status' => AdminStatusCast::Active,
            'level' => AdminTypeCast::Executive->getLevel(),
            'profit_share_percent' => 1.00,
            'profit_share_active' => false,
            'locale' => 'en',
            'preferences' => [],
            'two_factor_enabled' => false,
            'email_verified_at' => now(),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AdminTypeCast::SuperAdmin,
            'level' => AdminTypeCast::SuperAdmin->getLevel(),
            'profit_share_percent' => 0,
        ]);
    }

    public function ceo(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AdminTypeCast::Ceo,
            'level' => AdminTypeCast::Ceo->getLevel(),
            'profit_share_percent' => 15.00,
        ]);
    }

    public function director(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AdminTypeCast::Director,
            'level' => AdminTypeCast::Director->getLevel(),
            'profit_share_percent' => 10.00,
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AdminTypeCast::Manager,
            'level' => AdminTypeCast::Manager->getLevel(),
            'profit_share_percent' => 5.00,
        ]);
    }

    public function lead(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AdminTypeCast::Lead,
            'level' => AdminTypeCast::Lead->getLevel(),
            'profit_share_percent' => 3.00,
        ]);
    }

    public function executive(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AdminTypeCast::Executive,
            'level' => AdminTypeCast::Executive->getLevel(),
            'profit_share_percent' => 1.00,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdminStatusCast::Inactive,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdminStatusCast::Suspended,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('test-secret'),
        ]);
    }
}
