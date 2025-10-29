<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Mintreu\LaravelGeokit\Seeder\GeoKitSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            GeoKitSeeder::class,

            AdminSeeder::class,
            StageSeeder::class,
            FilterSeeder::class,  // required for product
            CategorySeeder::class,  // required for product

            UserSeeder::class,

            // MasterDemoProductSeeder::class,

            TaxCodeSeeder::class,  // required for product

            //ProductSeeder::class,
            ProductDemoSeeder::class,   // product seeder


            IntegrationSeeder::class,
            RecruitmentSeeder::class,

            PageSeeder::class,
            HelpDeskTopicSeeder::class,

            SaleSeeder::class,

            HelpDeskTicketSeeder::class,

            StaffSeeder::class,

            PostSeeder::class,




        ]);




    }
}
