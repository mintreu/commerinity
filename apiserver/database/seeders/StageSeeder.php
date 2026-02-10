<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Membership\Stage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StageSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command->info('Seeding membership stages...');

        $stages = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Begin your journey with basic access and features.',
                'base_price' => 25339,
                'discount' => 0,
                'tax_percentage' => 18,
                'tax_amount' => 4561,
                'price' => 29900, // ₹299
                'max_team_members' => 5,
                'matrix_width' => 5,
                'matrix_depth' => 1,
                'commission_rates' => ['level_1' => 5],
                'sponsor_bonus' => 0,
                'matching_bonus_percent' => 0,
                'matching_bonus_levels' => 0,
                'pool_contribution_percent' => 0,
                'level_achievement_bonus' => 0,
                'pv' => 0,
                'bv' => 0,
                'benefits' => ['Basic profile', 'View opportunities', 'Limited referrals'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Full membership access with all basic features.',
                'base_price' => 42288,
                'discount' => 0,
                'tax_percentage' => 18,
                'tax_amount' => 7612,
                'price' => 49900, // ₹499
                'max_team_members' => 25,
                'matrix_width' => 5,
                'matrix_depth' => 2,
                'commission_rates' => ['level_1' => 10, 'level_2' => 5],
                'sponsor_bonus' => 10000,
                'matching_bonus_percent' => 5,
                'matching_bonus_levels' => 1,
                'pool_contribution_percent' => 2,
                'level_achievement_bonus' => 5000,
                'pv' => 100,
                'bv' => 50,
                'benefits' => ['Full profile access', 'More referrals', 'Basic commission earnings', 'Standard support','Win Rewards'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Expert',
                'slug' => 'expert',
                'description' => 'Enhanced membership with shopping rewards and team benefits.',
                'base_price' => 84661,
                'discount' => 0,
                'tax_percentage' => 18,
                'tax_amount' => 15239,
                'price' => 99900, // ₹999
                'max_team_members' => 125,
                'matrix_width' => 5,
                'matrix_depth' => 3,
                'commission_rates' => ['level_1' => 12, 'level_2' => 7, 'level_3' => 3],
                'sponsor_bonus' => 30000,
                'matching_bonus_percent' => 7,
                'matching_bonus_levels' => 2,
                'pool_contribution_percent' => 3,
                'level_achievement_bonus' => 15000,
                'pv' => 300,
                'bv' => 150,
                'benefits' => ['Everything in Member', 'Higher commission rates', 'Priority support', 'Marketing materials', 'Team analytics','Win Prizes'],
                'is_active' => true,
                'sort_order' => 3,
            ]
        ];

        foreach ($stages as $stage) {
            Stage::updateOrCreate(
                ['slug' => $stage['slug']],
                array_merge($stage, [
                    'uuid' => Str::uuid()->toString(),
                ])
            );
        }

        $this->command->info('Seeded '.count($stages).' membership stages.');
    }
}

