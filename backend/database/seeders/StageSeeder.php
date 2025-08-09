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
        $this->makePlanWithLevel('Fresher',200,true);
        $this->makePlanWithLevel('Moderator',250);
        $this->makePlanWithLevel('Expert',300);
    }


    protected function makePlanWithLevel(string $planName, int $price = 100, bool $isDefault = false): void
    {
        $plan = Stage::create([
            'name' => ucfirst($planName),
            'url' => Str::slug($planName),
//            'base_price' => $price,
//            'discount' => 0,
//            'tax_percentage' => 0,
//            'tax_amount' => 0,
            'price' => $price,
            'max_team_members' => 780,
            'desc' => fake()->paragraph,
            'status' => true,
            'benefits' => ["Team Building" => "Active","Sponshorship" => "Active","Shopping Benefits" => "DeActive"],
            'accessibility' => ["Team Building" => "Active","Sponshorship" => "Active","Shopping Benefits" => "DeActive"],


        ]);

        $levelNames = ['Bronze', 'Silver', 'Gold', 'Diamond'];

        for ($x = 1; $x <= 4; $x++) {
            $levelName = $levelNames[($x - 1) % count($levelNames)]; // Cycle through names

            $level = $plan->levels()->create([
                'name' => ucfirst($planName) . ' ' . $levelName,
                'url' => Str::slug($planName . ' ' . $levelName),
//                'team_member_limit' => 5 ** $x,
//                'joining_bonus' => (4 - $x) == 0 ? 0.5 : (4 - $x),
                'status' => true,
                // Adjust other attributes as needed
            ]);
        }


    }






}
