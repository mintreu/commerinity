<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::factory(10) // Parent Categories
        ->has(
            Category::factory() // Child Categories
            ->count(5)
                ->hasProducts(5), // Products only on child categories
            'children'
        )
            ->create();
    }
}
