<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Casts\CommissionStatusCast;
use App\Casts\CommissionTypeCast;
use App\Models\Mlm\MlmCommission;
use App\Models\Mlm\MlmGenealogy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MlmCommission>
 */
class MlmCommissionFactory extends Factory
{
    protected $model = MlmCommission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $grossAmount = fake()->numberBetween(10000, 500000); // 100 to 5000 rupees in paisa

        return [
            'user_id' => User::factory(),
            'genealogy_id' => null,
            'from_user_id' => null,
            'commissionable_type' => null,
            'commissionable_id' => null,
            'type' => CommissionTypeCast::LEVEL_COMMISSION,
            'level' => fake()->numberBetween(1, 4),
            'rate_percent' => fake()->randomFloat(2, 1, 20),
            'base_amount' => fake()->numberBetween(100000, 1000000),
            'gross_amount' => $grossAmount,
            'tds_amount' => 0,
            'admin_fee' => 0,
            'net_amount' => $grossAmount,
            'status' => CommissionStatusCast::PENDING,
            'paid_via_transaction_id' => null,
            'paid_at' => null,
            'commission_date' => now()->toDateString(),
            'period_key' => now()->format('Y-m'),
            'description' => null,
            'metadata' => null,
            'approved_by' => null,
            'approved_at' => null,
            'reversed_commission_id' => null,
        ];
    }

    /**
     * For a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * With genealogy record
     */
    public function withGenealogy(MlmGenealogy $genealogy): static
    {
        return $this->state(fn (array $attributes) => [
            'genealogy_id' => $genealogy->id,
            'user_id' => $genealogy->user_id,
        ]);
    }

    /**
     * From a specific user's action
     */
    public function fromUser(User $fromUser): static
    {
        return $this->state(fn (array $attributes) => [
            'from_user_id' => $fromUser->id,
        ]);
    }

    /**
     * Set commission type
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'level' => $type === CommissionTypeCast::LEVEL_COMMISSION ? fake()->numberBetween(1, 4) : null,
        ]);
    }

    /**
     * Sponsor bonus type
     */
    public function sponsorBonus(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CommissionTypeCast::SPONSOR_BONUS,
            'level' => null,
            'description' => 'Direct sponsor bonus',
        ]);
    }

    /**
     * Level commission type
     */
    public function levelCommission(int $level = 1, float $rate = 10.0): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CommissionTypeCast::LEVEL_COMMISSION,
            'level' => $level,
            'rate_percent' => $rate,
            'description' => "Level {$level} commission ({$rate}%)",
        ]);
    }

    /**
     * Level achievement bonus
     */
    public function levelAchievement(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CommissionTypeCast::LEVEL_ACHIEVEMENT,
            'level' => null,
            'description' => 'Level achievement bonus',
        ]);
    }

    /**
     * Matching bonus
     */
    public function matchingBonus(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CommissionTypeCast::MATCHING_BONUS,
            'level' => null,
            'description' => 'Matching bonus',
        ]);
    }

    /**
     * Pool bonus
     */
    public function poolBonus(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CommissionTypeCast::POOL_BONUS,
            'level' => null,
            'description' => 'Pool distribution',
        ]);
    }

    /**
     * Set amounts (in paisa)
     */
    public function withAmounts(int $gross, int $tds = 0, int $adminFee = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'gross_amount' => $gross,
            'tds_amount' => $tds,
            'admin_fee' => $adminFee,
            'net_amount' => $gross - $tds - $adminFee,
        ]);
    }

    /**
     * Set base amount
     */
    public function withBaseAmount(int $baseAmount): static
    {
        return $this->state(fn (array $attributes) => [
            'base_amount' => $baseAmount,
        ]);
    }

    /**
     * Pending status
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommissionStatusCast::PENDING,
        ]);
    }

    /**
     * Approved status
     */
    public function approved(?int $approvedById = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommissionStatusCast::APPROVED,
            'approved_by' => $approvedById,
            'approved_at' => now(),
        ]);
    }

    /**
     * Processing status
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommissionStatusCast::PROCESSING,
            'approved_at' => now()->subMinutes(5),
        ]);
    }

    /**
     * Paid status
     */
    public function paid(?int $transactionId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommissionStatusCast::PAID,
            'paid_via_transaction_id' => $transactionId,
            'paid_at' => now(),
            'approved_at' => now()->subHour(),
        ]);
    }

    /**
     * Held status
     */
    public function held(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommissionStatusCast::HELD,
        ]);
    }

    /**
     * Cancelled status
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommissionStatusCast::CANCELLED,
        ]);
    }

    /**
     * Reversed status
     */
    public function reversed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommissionStatusCast::REVERSED,
        ]);
    }

    /**
     * For a specific period
     */
    public function forPeriod(string $periodKey): static
    {
        return $this->state(fn (array $attributes) => [
            'period_key' => $periodKey,
        ]);
    }

    /**
     * For this month
     */
    public function thisMonth(): static
    {
        return $this->forPeriod(now()->format('Y-m'));
    }

    /**
     * For last month
     */
    public function lastMonth(): static
    {
        return $this->forPeriod(now()->subMonth()->format('Y-m'));
    }

    /**
     * With TDS deducted
     */
    public function withTds(float $rate = 10.0): static
    {
        return $this->state(function (array $attributes) use ($rate) {
            $tds = (int) round($attributes['gross_amount'] * ($rate / 100));

            return [
                'tds_amount' => $tds,
                'net_amount' => $attributes['gross_amount'] - $tds - ($attributes['admin_fee'] ?? 0),
            ];
        });
    }

    /**
     * With admin fee
     */
    public function withAdminFee(int $fee): static
    {
        return $this->state(function (array $attributes) use ($fee) {
            return [
                'admin_fee' => $fee,
                'net_amount' => $attributes['gross_amount'] - ($attributes['tds_amount'] ?? 0) - $fee,
            ];
        });
    }
}
