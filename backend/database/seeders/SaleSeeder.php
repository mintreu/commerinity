<?php

namespace Database\Seeders;

use App\Models\Lifecycle\Level;
use App\Models\Promotion\Sale;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targets = Level::all();

//        $sales = Sale::factory()
//            ->count(10)
//            ->create()
//            ->each(function (Sale $sale) use ($targets) {
//                $sale->targets()->attach($targets->random());
//            });


        Sale::factory()
            ->count(10)
            ->create()
            ->each(function (Sale $sale) use ($targets) {
                // Attach 1–3 random levels to each sale
                $randomTargets = $targets->random(rand(1, min(3, $targets->count())));

                $sale->targets()->attach($randomTargets);
            });


    }
}
