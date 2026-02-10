<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Casts\WalletStatusCast;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'walletable_type' => User::class,
            'walletable_id' => User::factory(),
            'balance' => 0,
            'hold_balance' => 0,
            'total_credited' => 0,
            'total_debited' => 0,
            'points' => 0,
            'pin' => null,
            'pin_updated_at' => null,
            'currency' => 'INR',
            'status' => WalletStatusCast::ACTIVE,
            'metadata' => null,
        ];
    }

    /**
     * For a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'walletable_type' => User::class,
            'walletable_id' => $user->id,
        ]);
    }

    /**
     * For a polymorphic owner
     */
    public function forOwner($owner): static
    {
        return $this->state(fn (array $attributes) => [
            'walletable_type' => get_class($owner),
            'walletable_id' => $owner->getKey(),
        ]);
    }

    /**
     * With a specific balance (in paisa)
     */
    public function withBalance(int $balance): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $balance,
            'total_credited' => $balance,
        ]);
    }

    /**
     * With hold balance (in paisa)
     */
    public function withHoldBalance(int $holdBalance): static
    {
        return $this->state(fn (array $attributes) => [
            'hold_balance' => $holdBalance,
        ]);
    }

    /**
     * With credits and debits
     */
    public function withHistory(int $totalCredited, int $totalDebited): static
    {
        return $this->state(fn (array $attributes) => [
            'total_credited' => $totalCredited,
            'total_debited' => $totalDebited,
            'balance' => $totalCredited - $totalDebited,
        ]);
    }

    /**
     * With points
     */
    public function withPoints(int $points): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => $points,
        ]);
    }

    /**
     * Active status
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WalletStatusCast::ACTIVE,
        ]);
    }

    /**
     * Suspended status
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WalletStatusCast::SUSPENDED,
        ]);
    }

    /**
     * Frozen status
     */
    public function frozen(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WalletStatusCast::FROZEN,
        ]);
    }

    /**
     * Closed status
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WalletStatusCast::CLOSED,
        ]);
    }

    /**
     * With PIN set
     */
    public function withPin(string $pin = '123456'): static
    {
        return $this->state(fn (array $attributes) => [
            'pin' => bcrypt($pin),
            'pin_updated_at' => now(),
        ]);
    }

    /**
     * Rich wallet (for testing)
     */
    public function rich(): static
    {
        $credited = fake()->numberBetween(10000000, 100000000); // 1L to 10L rupees
        $debited = fake()->numberBetween(1000000, $credited - 1000000);

        return $this->state(fn (array $attributes) => [
            'balance' => $credited - $debited,
            'total_credited' => $credited,
            'total_debited' => $debited,
            'points' => fake()->numberBetween(100, 10000),
        ]);
    }

    /**
     * Empty wallet
     */
    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => 0,
            'hold_balance' => 0,
            'total_credited' => 0,
            'total_debited' => 0,
            'points' => 0,
        ]);
    }
}

