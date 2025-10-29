<?php

namespace Database\Seeders;

use App\Models\Lifecycle\Stage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StageSeeder extends Seeder
{



    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->makePlanWithLevel('Fresher',20000,true);
        $this->makePlanWithLevel('Moderator',25000);
        $this->makePlanWithLevel('Expert',30000);
    }


    protected function makePlanWithLevel(string $planName, int $price = 100, bool $isDefault = false): void
    {
        $plan = Stage::create([
            'name' => ucfirst($planName),
            'url' => Str::slug($planName),
            'price' => $price,
            'max_team_members' => 780,
            'desc' => fake()->paragraph,
            'status' => true,
            'benefits' => [
                ['name' => 'Team Building', 'slug' => 'team-building', 'value' => 0],
                ['name' => 'Sponsorship', 'slug' => 'sponsorship', 'value' => 0],
                ['name' => 'Shopping Benefits', 'slug' => 'shopping-benefits', 'value' => 1], // Always active
                ['name' => 'Affiliate Commissions', 'slug' => 'affiliate-commissions', 'value' => 0],
                ['name' => 'Exclusive Tools', 'slug' => 'exclusive-tools', 'value' => 0],
                ['name' => 'Network Growth', 'slug' => 'network-growth', 'value' => 0],
                ['name' => 'Early Access Features', 'slug' => 'early-access-features', 'value' => 0],
                ['name' => 'Premium Support', 'slug' => 'premium-support', 'value' => 0],
                ['name' => 'Career Opportunities', 'slug' => 'career-opportunities', 'value' => 0],
            ],

            'accessibility' => [
                ['name' => 'Team Building', 'slug' => 'team-building', 'value' => 0],
                ['name' => 'Sponsorship', 'slug' => 'sponsorship', 'value' => 0],
                ['name' => 'Shopping Benefits', 'slug' => 'shopping-benefits', 'value' => 1],
                ['name' => 'Affiliate Commissions', 'slug' => 'affiliate-commissions', 'value' => 0],
                ['name' => 'Exclusive Tools', 'slug' => 'exclusive-tools', 'value' => 0],
                ['name' => 'Network Growth', 'slug' => 'network-growth', 'value' => 0],
                ['name' => 'Early Access Features', 'slug' => 'early-access-features', 'value' => 0],
                ['name' => 'Premium Support', 'slug' => 'premium-support', 'value' => 0],
                ['name' => 'Career Opportunities', 'slug' => 'career-opportunities', 'value' => 0],
            ],



        ]);





        $levelNames = ['Bronze', 'Silver', 'Gold', 'Diamond'];

        for ($x = 1; $x <= config('app.matrix',4); $x++) {
            $levelName = $levelNames[($x - 1) % count($levelNames)]; // Cycle through names

            $level = $plan->levels()->create([
                'name' => ucfirst($planName) . ' ' . $levelName,
                'url' => Str::slug($planName . ' ' . $levelName),
                'team_member_limit' => 5 ** $x,
                'joining_bonus' => (4 - $x) == 0 ? 0.5 : (4 - $x),
                'status' => true,
                'validate_years' => max(1, min($x, 3))
                // Adjust other attributes as needed
            ]);
        }


    }






}
