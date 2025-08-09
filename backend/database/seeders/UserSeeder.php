<?php

namespace Database\Seeders;

use App\Casts\AuthStatusCast;
use App\Casts\AuthTypeCast;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'demouser@example.com',
            'status'    => AuthStatusCast::DRAFT
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'applicant@example.com',
            'status'    => AuthStatusCast::DRAFT,
            'type'  => AuthTypeCast::APPLICANT
        ]);

        User::factory()->create([
            'name' => 'Krishanu Bhattacharya',
            'email' => 'test@example.com',
            'mobile' => '9800777600',
            'status'    => AuthStatusCast::DRAFT,
            'type'  => AuthTypeCast::REGULAR
        ]);
    }
}
