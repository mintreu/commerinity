<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Test Staff

        $testStaff = Staff::factory()->create([
            'email' => 'test@staff.com',
            'password' => 'password'
        ]);


        // Random Staff
        Staff::factory(15)->create();


    }
}
