<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo users for each type

        // 1. Regular Customer
        User::create([
            'name' => 'Regular Customer',
            'mobile' => '+919876543210',
            'email' => 'regular@demo.com',
            'password' => Hash::make('password'),
            'type' => UserTypeCast::REGULAR->value,
            'status' => UserStatusCast::DRAFT->value,
            'mobile_verified_at' => now(),
            'email_verified_at' => now(),
            'onboarded' => false,
        ]);

        // 2. Member
        User::create([
            'name' => 'Member Demo',
            'mobile' => '+919876543211',
            'email' => 'member@demo.com',
            'password' => Hash::make('password'),
            'type' => UserTypeCast::MEMBER->value,
            'status' => UserStatusCast::ACTIVE->value,
            'mobile_verified_at' => now(),
            'email_verified_at' => now(),
            'onboarded' => true,
        ]);

        // 3. Promoter
        User::create([
            'name' => 'Promoter Demo',
            'mobile' => '+919876543212',
            'email' => 'promoter@demo.com',
            'password' => Hash::make('password'),
            'type' => UserTypeCast::PROMOTER->value,
            'status' => UserStatusCast::ACTIVE->value,
            'mobile_verified_at' => now(),
            'email_verified_at' => now(),
            'onboarded' => true,
        ]);

        // 4. Advisor
        User::create([
            'name' => 'Advisor Demo',
            'mobile' => '+919876543213',
            'email' => 'advisor@demo.com',
            'password' => Hash::make('password'),
            'type' => UserTypeCast::ADVISOR->value,
            'status' => UserStatusCast::ACTIVE->value,
            'mobile_verified_at' => now(),
            'email_verified_at' => now(),
            'onboarded' => true,
        ]);

        // 5. Mentor
        User::create([
            'name' => 'Mentor Demo',
            'mobile' => '+919876543214',
            'email' => 'mentor@demo.com',
            'password' => Hash::make('password'),
            'type' => UserTypeCast::MENTOR->value,
            'status' => UserStatusCast::ACTIVE->value,
            'mobile_verified_at' => now(),
            'email_verified_at' => now(),
            'onboarded' => true,
        ]);




        // Seed Extra Users

        User::create([
            'name' => 'Partha',
            'mobile' => '+919876543215',
            'email' => 'partha@demo.com',
            'password' => Hash::make('password'),
            'type' => UserTypeCast::REGULAR->value,
            'status' => UserStatusCast::ACTIVE->value,
            'mobile_verified_at' => now(),
            'email_verified_at' => now(),
            'onboarded' => true,
        ]);


        User::create([
            'name' => 'Saneep',
            'mobile' => '+919876543212',
            'email' => 'sandeep@demo.com',
            'password' => Hash::make('password'),
            'type' => UserTypeCast::REGULAR->value,
            'status' => UserStatusCast::ACTIVE->value,
            'mobile_verified_at' => now(),
            'email_verified_at' => now(),
            'onboarded' => true,
        ]);


        User::create([
            'name' => 'Akash',
            'mobile' => '+919876543209',
            'email' => 'akash@demo.com',
            'password' => Hash::make('password'),
            'type' => UserTypeCast::REGULAR->value,
            'status' => UserStatusCast::ACTIVE->value,
            'mobile_verified_at' => now(),
            'email_verified_at' => now(),
            'onboarded' => true,
        ]);

        User::create([
            'name' => 'Saheb',
            'mobile' => '+919876543218',
            'email' => 'saheb@demo.com',
            'password' => Hash::make('password'),
            'type' => UserTypeCast::REGULAR->value,
            'status' => UserStatusCast::ACTIVE->value,
            'mobile_verified_at' => now(),
            'email_verified_at' => now(),
            'onboarded' => true,
        ]);

    }
}
