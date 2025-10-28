<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Mintreu\LaravelNaukriManager\Models\Naukri;
use Mintreu\LaravelRecruitment\Models\Recruitment;

class RecruitmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        Recruitment::factory(30)->create();
    }
}
