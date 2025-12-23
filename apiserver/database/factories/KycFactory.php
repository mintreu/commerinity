<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class KycFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kycable_type' => User::class,
            'kycable_id' => User::factory(),
            'kyc_type' => 'personal',
            'pan_number' => strtoupper(fake()->bothify('?????####?')),
            'aadhaar_number' => fake()->numerify('############'),
            'status' => 'pending',
            'submitted_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => 'rejected',
            'rejection_reason' => 'Invalid documents provided',
            'reviewed_at' => now(),
        ]);
    }

    public function business(): static
    {
        return $this->state([
            'kyc_type' => 'business',
            'company_name' => fake()->company(),
            'company_type' => fake()->randomElement(['sole_proprietor', 'partnership', 'llp', 'private_limited']),
            'gst_number' => fake()->numerify('##').strtoupper(fake()->bothify('?????####?')).'1Z'.fake()->randomDigit(),
        ]);
    }
}
