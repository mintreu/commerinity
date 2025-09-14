<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            AdminSeeder::class,
            FilterSeeder::class,
            CategorySeeder::class,
            //ProductSeeder::class,
            ProductDemoSeeder::class,
            IntegrationSeeder::class,
            NaukriSeeder::class,
            StageSeeder::class,
            PageSeeder::class,
            HelpDeskTopicSeeder::class,
            UserSeeder::class,
            SaleSeeder::class,

            HelpDeskTicketSeeder::class

        ]);




    }
}
