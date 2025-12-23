<?php

declare(strict_types=1);

namespace Database\Factories\Membership;

use App\Models\Membership\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stage>
 */
class StageFactory extends Factory
{
    protected $model = Stage::class;

    /**
     * Stage names with pricing in paisa (100 paisa = 1 rupee)
     */
    private static array $stageConfig = [
        1 => ['name' => 'Basic', 'price' => 99900, 'pv' => 100],        // ₹999
        2 => ['name' => 'Premium', 'price' => 299900, 'pv' => 300],     // ₹2,999
        3 => ['name' => 'Elite', 'price' => 599900, 'pv' => 600],       // ₹5,999
        4 => ['name' => 'Royal', 'price' => 999900, 'pv' => 1000],      // ₹9,999
    ];

    private static int $sortCounter = 0;

    public function definition(): array
    {
        self::$sortCounter++;
        $stageNum = min(self::$sortCounter, 4);
        $config = self::$stageConfig[$stageNum];

        $basePrice = $config['price'];
        $discount = 0;
        $taxPercent = 18;
        $taxAmount = (int) round(($basePrice - $discount) * ($taxPercent / 100));
        $finalPrice = $basePrice - $discount + $taxAmount;

        return [
            'name' => $config['name'],
            'slug' => strtolower($config['name']),
            'description' => "The {$config['name']} membership stage",
            'base_price' => $basePrice,
            'discount' => $discount,
            'tax_percentage' => $taxPercent,
            'tax_amount' => $taxAmount,
            'price' => $finalPrice,
            'max_team_members' => 780, // 5 + 25 + 125 + 625
            'matrix_width' => 5,
            'matrix_depth' => 4,
            'commission_rates' => [
                'level_1' => 10, // 10% for level 1
                'level_2' => 5,  // 5% for level 2
                'level_3' => 3,  // 3% for level 3
                'level_4' => 2,  // 2% for level 4
            ],
            'sponsor_bonus' => [
                'type' => 'percent',
                'value' => 15, // 15% sponsor bonus
            ],
            'matching_bonus_percent' => 10,
            'matching_bonus_levels' => 2,
            'pool_contribution_percent' => 2,
            'level_achievement_bonus' => [
                1 => 50000,   // ₹500 for Bronze
                2 => 100000,  // ₹1,000 for Silver
                3 => 200000,  // ₹2,000 for Gold
                4 => 500000,  // ₹5,000 for Diamond
            ],
            'pv' => $config['pv'],
            'bv' => $config['pv'],
            'benefits' => [
                'commission_enabled' => true,
                'team_building' => true,
                'training_access' => true,
            ],
            'accessibility' => [
                'public' => true,
            ],
            'sort_order' => self::$sortCounter,
            'is_active' => true,
            'is_default' => self::$sortCounter === 1,
        ];
    }

    /**
     * Create a basic stage
     */
    public function basic(): static
    {
        return $this->state(fn () => [
            'name' => 'Basic',
            'slug' => 'basic',
            'base_price' => 99900,
            'sort_order' => 1,
            'is_default' => true,
        ]);
    }

    /**
     * Create a premium stage
     */
    public function premium(): static
    {
        return $this->state(fn () => [
            'name' => 'Premium',
            'slug' => 'premium',
            'base_price' => 299900,
            'sort_order' => 2,
            'is_default' => false,
        ]);
    }

    /**
     * Create an elite stage
     */
    public function elite(): static
    {
        return $this->state(fn () => [
            'name' => 'Elite',
            'slug' => 'elite',
            'base_price' => 599900,
            'sort_order' => 3,
            'is_default' => false,
        ]);
    }

    /**
     * Create with specific upgrade path
     */
    public function upgradesTo(Stage $nextStage): static
    {
        return $this->state(fn () => [
            'upgrade_to_stage_id' => $nextStage->id,
            'upgrade_price_difference' => $nextStage->price - $this->faker->numberBetween(90000, 300000),
        ]);
    }

    /**
     * Create with 4 levels already created
     */
    public function withLevels(): static
    {
        return $this->afterCreating(function (Stage $stage) {
            $levelNames = ['Bronze', 'Silver', 'Gold', 'Diamond'];
            $globalRankBase = ($stage->sort_order - 1) * 4;

            foreach ($levelNames as $index => $name) {
                $levelNumber = $index + 1;

                $stage->levels()->create([
                    'name' => $name,
                    'full_name' => "{$stage->name} {$name}",
                    'global_rank' => $globalRankBase + $levelNumber,
                    'level_number' => $levelNumber,
                    'slug' => strtolower("{$stage->slug}-{$name}"),
                    'description' => "{$stage->name} stage - {$name} level",
                    'team_member_limit' => (int) pow(5, $levelNumber),
                    'min_direct_referrals' => $levelNumber,
                    'min_active_directs' => max(1, $levelNumber - 1),
                    'min_personal_purchase' => 0,
                    'min_team_sales' => 0,
                    'validity_days' => 365,
                    'joining_bonus' => 0,
                    'purchase_commission' => 0,
                    'recruitment_commission' => 0,
                    'depth_commissions' => $stage->commission_rates,
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
                ]);
            }
        });
    }

    /**
     * Reset the sort counter (for fresh test runs)
     */
    public static function resetCounter(): void
    {
        self::$sortCounter = 0;
    }
}
