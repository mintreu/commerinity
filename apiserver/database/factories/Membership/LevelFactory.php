<?php

declare(strict_types=1);

namespace Database\Factories\Membership;

use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Level>
 */
class LevelFactory extends Factory
{
    protected $model = Level::class;

    private static array $levelNames = ['Bronze', 'Silver', 'Gold', 'Diamond'];

    public function definition(): array
    {
        $levelNumber = $this->faker->numberBetween(1, 4);
        $name = self::$levelNames[$levelNumber - 1];

        return [
            'stage_id' => Stage::factory(),
            'name' => $name,
            'full_name' => "Test {$name}",
            'global_rank' => $levelNumber,
            'level_number' => $levelNumber,
            'slug' => strtolower($name),
            'description' => "{$name} level description",
            'team_member_limit' => (int) pow(5, $levelNumber),
            'min_direct_referrals' => $levelNumber,
            'min_active_directs' => max(1, $levelNumber - 1),
            'min_personal_purchase' => 0,
            'min_team_sales' => 0,
            'validity_days' => 365,
            'joining_bonus' => 0,
            'purchase_commission' => 0,
            'recruitment_commission' => 0,
            'depth_commissions' => [
                'level_1' => 10,
                'level_2' => 5,
                'level_3' => 3,
                'level_4' => 2,
            ],
            'commission_multiplier' => 1 + ($levelNumber * 0.1),
            'level_benefits' => [
                'commission_boost' => $levelNumber * 10,
            ],
            'badge_icon' => match ($name) {
                'Bronze' => '🥉',
                'Silver' => '🥈',
                'Gold' => '🥇',
                'Diamond' => '💎',
            },
            'badge_color' => match ($name) {
                'Bronze' => '#CD7F32',
                'Silver' => '#C0C0C0',
                'Gold' => '#FFD700',
                'Diamond' => '#B9F2FF',
            },
            'sort_order' => $levelNumber,
            'is_active' => true,
        ];
    }

    /**
     * Create a Bronze level (Level 1)
     */
    public function bronze(): static
    {
        return $this->state(fn () => [
            'name' => 'Bronze',
            'level_number' => 1,
            'team_member_limit' => 5,
            'min_direct_referrals' => 1,
            'min_active_directs' => 1,
            'badge_icon' => '🥉',
            'badge_color' => '#CD7F32',
        ]);
    }

    /**
     * Create a Silver level (Level 2)
     */
    public function silver(): static
    {
        return $this->state(fn () => [
            'name' => 'Silver',
            'level_number' => 2,
            'team_member_limit' => 25,
            'min_direct_referrals' => 2,
            'min_active_directs' => 1,
            'badge_icon' => '🥈',
            'badge_color' => '#C0C0C0',
        ]);
    }

    /**
     * Create a Gold level (Level 3)
     */
    public function gold(): static
    {
        return $this->state(fn () => [
            'name' => 'Gold',
            'level_number' => 3,
            'team_member_limit' => 125,
            'min_direct_referrals' => 3,
            'min_active_directs' => 2,
            'badge_icon' => '🥇',
            'badge_color' => '#FFD700',
        ]);
    }

    /**
     * Create a Diamond level (Level 4)
     */
    public function diamond(): static
    {
        return $this->state(fn () => [
            'name' => 'Diamond',
            'level_number' => 4,
            'team_member_limit' => 625,
            'min_direct_referrals' => 4,
            'min_active_directs' => 3,
            'badge_icon' => '💎',
            'badge_color' => '#B9F2FF',
        ]);
    }

    /**
     * Create for a specific stage
     */
    public function forStage(Stage $stage): static
    {
        return $this->state(fn () => [
            'stage_id' => $stage->id,
        ]);
    }

    /**
     * Create with specific level number and derive other fields
     */
    public function levelNumber(int $number): static
    {
        $name = self::$levelNames[$number - 1] ?? 'Unknown';

        return $this->state(fn () => [
            'name' => $name,
            'level_number' => $number,
            'team_member_limit' => (int) pow(5, $number),
            'min_direct_referrals' => $number,
            'min_active_directs' => max(1, $number - 1),
            'sort_order' => $number,
        ]);
    }
}
