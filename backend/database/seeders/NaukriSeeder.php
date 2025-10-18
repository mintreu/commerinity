<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Mintreu\LaravelNaukriManager\Models\Naukri;

class NaukriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        Naukri::factory(30)->create();
    }
}
