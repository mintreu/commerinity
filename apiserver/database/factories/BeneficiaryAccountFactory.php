<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Casts\BeneficiaryStatusCast;
use App\Casts\BeneficiaryTypeCast;
use App\Models\BeneficiaryAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BeneficiaryAccount>
 */
class BeneficiaryAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([
            BeneficiaryTypeCast::SAVINGS,
            BeneficiaryTypeCast::CURRENT,
            BeneficiaryTypeCast::UPI,
        ]);

        $isBank = $type->isBank();

        return [
            'accountable_type' => User::class,
            'accountable_id' => User::factory(),
            'type' => $type,
            'account_number' => $isBank ? fake()->numerify('############') : null,
            'ifsc_code' => $isBank ? 'HDFC0'.fake()->numerify('######') : null,
            'bank_name' => $isBank ? fake()->randomElement(['HDFC Bank', 'ICICI Bank', 'SBI', 'Axis Bank', 'Kotak Bank']) : null,
            'branch_name' => $isBank ? fake()->city().' Branch' : null,
            'upi_id' => ! $isBank ? fake()->userName().'@upi' : null,
            'holder_name' => fake()->name(),
            'status' => BeneficiaryStatusCast::PENDING,
            'is_default' => false,
            'metadata' => [],
        ];
    }

    /**
     * Bank account (savings or current)
     */
    public function bank(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => fake()->randomElement([BeneficiaryTypeCast::SAVINGS, BeneficiaryTypeCast::CURRENT]),
            'account_number' => fake()->numerify('############'),
            'ifsc_code' => 'HDFC0'.fake()->numerify('######'),
            'bank_name' => fake()->randomElement(['HDFC Bank', 'ICICI Bank', 'SBI']),
            'branch_name' => fake()->city().' Branch',
            'upi_id' => null,
        ]);
    }

    /**
     * UPI account
     */
    public function upi(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BeneficiaryTypeCast::UPI,
            'account_number' => null,
            'ifsc_code' => null,
            'bank_name' => null,
            'branch_name' => null,
            'upi_id' => fake()->userName().'@upi',
        ]);
    }

    /**
     * Verified status
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BeneficiaryStatusCast::VERIFIED,
            'verified_at' => now(),
        ]);
    }

    /**
     * Pending status
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BeneficiaryStatusCast::PENDING,
            'verified_at' => null,
        ]);
    }

    /**
     * Rejected status
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BeneficiaryStatusCast::REJECTED,
            'rejection_reason' => 'Invalid account details',
            'verified_at' => null,
        ]);
    }

    /**
     * Default account
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}

