<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Membership\Level;
use App\Models\Membership\Stage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LevelSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command->info('Seeding membership levels...');

        // Get stages
        $stages = Stage::pluck('id', 'slug');

        if ($stages->isEmpty()) {
            $this->command->warn('No stages found. Run StageSeeder first.');

            return;
        }

        $levelNames = ['Bronze', 'Silver', 'Gold', 'Diamond'];
        $levelColors = ['#CD7F32', '#C0C0C0', '#FFD700', '#B9F2FF'];

        $globalRank = 0;
        $count = 0;

        foreach ($stages as $stageSlug => $stageId) {
            foreach ($levelNames as $levelNumber => $levelName) {
                $globalRank++;
                $levelNum = $levelNumber + 1;

                Level::updateOrCreate(
                    [
                        'stage_id' => $stageId,
                        'level_number' => $levelNum,
                    ],
                    [
                        'uuid' => Str::uuid()->toString(),
                        'name' => $levelName,
                        'full_name' => ucfirst($stageSlug).' '.$levelName,
                        'global_rank' => $globalRank,
                        'slug' => $stageSlug.'-'.strtolower($levelName),
                        'description' => "{$levelName} level in {$stageSlug} stage",
                        'team_member_limit' => pow(5, $levelNum), // 5, 25, 125, 625
                        'min_direct_referrals' => $levelNum,
                        'min_active_directs' => max(1, $levelNum - 1),
                        'min_personal_purchase' => $levelNum * 10000, // 100, 200, 300, 400 INR in paisa
                        'min_team_sales' => $levelNum * 50000,
                        'validity_days' => 365,
                        'joining_bonus' => $levelNum * 100,
                        'purchase_commission' => 5 + ($levelNum * 2), // 7%, 9%, 11%, 13%
                        'recruitment_commission' => 3 + $levelNum, // 4%, 5%, 6%, 7%
                        'depth_commissions' => array_fill(0, $levelNum, 2), // 2% for each level
                        'commission_multiplier' => 1 + ($levelNum * 0.1), // 1.1, 1.2, 1.3, 1.4
                        'level_benefits' => [
                            "Level {$levelNum} benefits",
                            'Team capacity: '.pow(5, $levelNum),
                            'Commission: '.(5 + ($levelNum * 2)).'%',
                        ],
                        'badge_icon' => 'heroicon-o-star',
                        'badge_color' => $levelColors[$levelNumber],
                        'sort_order' => $globalRank,
                        'is_active' => true,
                    ]
                );
                $count++;
            }
        }

        $this->command->info("Seeded {$count} membership levels across ".count($stages).' stages.');
    }
}

