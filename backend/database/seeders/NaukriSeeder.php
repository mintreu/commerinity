<?php

namespace Database\Seeders;

use App\Models\Naukri;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NaukriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Naukri::factory(20)->create();
    }
}
