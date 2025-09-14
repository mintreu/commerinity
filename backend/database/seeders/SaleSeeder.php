<?php

namespace Database\Seeders;

use App\Models\Lifecycle\Level;
use Illuminate\Database\Seeder;
use Mintreu\LaravelCommerinity\Models\Sale;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targets = Level::all();


        Sale::factory()
            ->count(1)
            ->create()
            ->each(function (Sale $sale) use ($targets) {
                // Attach 1–3 random levels to each sale
                $randomTargets = $targets->random(rand(1, min(3, $targets->count())));

                $sale->targets()->attach($randomTargets);
            });


    }
}
