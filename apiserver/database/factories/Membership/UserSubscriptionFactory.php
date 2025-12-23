<?php

declare(strict_types=1);

namespace Database\Factories\Membership;

use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSubscription>
 */
class UserSubscriptionFactory extends Factory
{
    protected $model = UserSubscription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'stage_id' => Stage::factory(),
            'level_id' => Level::factory(),
            'base_price' => 99900, // ₹999
            'discount' => 0,
            'tax_amount' => 17982, // 18% GST
            'amount' => 117882,
            'is_paid' => false,
            'status' => UserSubscription::STATUS_PENDING,
            'personal_pv' => 0,
            'team_pv' => 0,
            'total_commission_earned' => 0,
            'current_month_commission' => 0,
            'renewal_count' => 0,
        ];
    }

    /**
     * Set subscription as active
     */
    public function active(): static
    {
        return $this->state(fn () => [
            'status' => UserSubscription::STATUS_ACTIVE,
            'is_paid' => true,
            'paid_at' => now(),
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
        ]);
    }

    /**
     * Set subscription as pending payment
     */
    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => UserSubscription::STATUS_PENDING,
            'is_paid' => false,
        ]);
    }

    /**
     * Set subscription as expired
     */
    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => UserSubscription::STATUS_EXPIRED,
            'is_paid' => true,
            'paid_at' => now()->subYear(),
            'starts_at' => now()->subYear(),
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Set subscription with specific stage
     */
    public function forStage(Stage $stage): static
    {
        $firstLevel = $stage->getFirstLevel();

        return $this->state(fn () => [
            'stage_id' => $stage->id,
            'level_id' => $firstLevel?->id,
            'current_level_id' => $firstLevel?->id,
            'base_price' => $stage->base_price,
            'discount' => $stage->discount,
            'tax_amount' => $stage->tax_amount,
            'amount' => $stage->price,
        ]);
    }

    /**
     * Set subscription for specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Set as a renewal of previous subscription
     */
    public function renewal(UserSubscription $previous): static
    {
        return $this->state(fn () => [
            'user_id' => $previous->user_id,
            'stage_id' => $previous->stage_id,
            'level_id' => $previous->level_id,
            'current_level_id' => $previous->current_level_id,
            'highest_level_id' => $previous->highest_level_id,
            'previous_subscription_id' => $previous->id,
            'renewal_count' => $previous->renewal_count + 1,
            'personal_pv' => $previous->personal_pv,
            'team_pv' => $previous->team_pv,
        ]);
    }

    /**
     * Set subscription with originator (agent/advisor)
     */
    public function withOriginator(User $originator): static
    {
        return $this->state(fn () => [
            'originator_type' => User::class,
            'originator_id' => $originator->id,
        ]);
    }

    /**
     * Set subscription at specific level
     */
    public function atLevel(Level $level): static
    {
        return $this->state(fn () => [
            'stage_id' => $level->stage_id,
            'level_id' => $level->id,
            'current_level_id' => $level->id,
        ]);
    }

    /**
     * Set subscription with commission earnings
     */
    public function withCommissions(int $totalEarned, int $thisMonth = 0): static
    {
        return $this->state(fn () => [
            'total_commission_earned' => $totalEarned,
            'current_month_commission' => $thisMonth,
        ]);
    }

    /**
     * Create subscription that's about to expire (in given days)
     */
    public function expiringIn(int $days): static
    {
        return $this->state(fn () => [
            'status' => UserSubscription::STATUS_ACTIVE,
            'is_paid' => true,
            'paid_at' => now()->subYear()->addDays($days),
            'starts_at' => now()->subYear()->addDays($days),
            'expires_at' => now()->addDays($days),
        ]);
    }
}
