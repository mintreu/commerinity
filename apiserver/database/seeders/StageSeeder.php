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
                'base_price' => 0,
                'discount' => 0,
                'tax_percentage' => 18,
                'tax_amount' => 0,
                'price' => 0, // Free
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
                'name' => 'Member',
                'slug' => 'member',
                'description' => 'Full membership access with all basic features.',
                'base_price' => 84700,
                'discount' => 0,
                'tax_percentage' => 18,
                'tax_amount' => 15200,
                'price' => 99900, // 999 INR in paisa
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
                'benefits' => ['Full profile access', 'Unlimited referrals', 'Basic commission earnings', 'Standard support'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Promoter',
                'slug' => 'promoter',
                'description' => 'Enhanced membership with promotional benefits.',
                'base_price' => 254150,
                'discount' => 0,
                'tax_percentage' => 18,
                'tax_amount' => 45750,
                'price' => 299900, // 2999 INR in paisa
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
                'benefits' => ['Everything in Member', 'Higher commission rates', 'Priority support', 'Marketing materials', 'Team analytics'],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Advisor',
                'slug' => 'advisor',
                'description' => 'Premium membership with advisory privileges.',
                'base_price' => 508400,
                'discount' => 0,
                'tax_percentage' => 18,
                'tax_amount' => 91500,
                'price' => 599900, // 5999 INR in paisa
                'max_team_members' => 625,
                'matrix_width' => 5,
                'matrix_depth' => 4,
                'commission_rates' => ['level_1' => 15, 'level_2' => 10, 'level_3' => 5, 'level_4' => 2],
                'sponsor_bonus' => 60000,
                'matching_bonus_percent' => 10,
                'matching_bonus_levels' => 3,
                'pool_contribution_percent' => 4,
                'level_achievement_bonus' => 30000,
                'pv' => 600,
                'bv' => 300,
                'benefits' => ['Everything in Promoter', 'Premium commission rates', 'Dedicated support', 'Training materials', 'Leadership bonuses', 'Monthly webinars'],
                'is_active' => true,
                'sort_order' => 4,
            ],
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
