<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\AdminTypeCast;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SuperAdmin (Company Account) - UNIQUE
        $superAdmin = Admin::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Commerinity Pro',
                'password' => bcrypt('SuperAdmin@123'),
                'type' => AdminTypeCast::SuperAdmin,
                'level' => 0,
                'profit_share_percent' => 0,
                'locale' => 'en',
            ]
        );

        // Create SuperAdmin wallet (company fund)
        $superAdmin->wallet()->firstOrCreate(
            ['walletable_type' => Admin::class, 'walletable_id' => $superAdmin->id],
            [
                'uuid' => Str::uuid()->toString(),
                'balance' => 0,
                'currency' => 'INR',
                'status' => 'active',
            ]
        );

        $this->command->info('SuperAdmin created: superadmin@example.com');

        // 2. CEO
        $ceo = Admin::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Chief Executive',
                'password' => bcrypt('Admin@123'),
                'type' => AdminTypeCast::Ceo,
                'level' => 1,
                'created_by_admin_id' => $superAdmin->id,
                'profit_share_percent' => 15.00,
                'locale' => 'en',
            ]
        );

        // Create CEO wallet
        $ceo->wallet()->firstOrCreate(
            ['walletable_type' => Admin::class, 'walletable_id' => $ceo->id],
            [
                'uuid' => Str::uuid()->toString(),
                'balance' => 0,
                'currency' => 'INR',
                'status' => 'active',
            ]
        );

        $this->command->info('CEO created: admin@example.com');



        // 3. DIRECTOR
        $ceo = Admin::firstOrCreate(
            ['email' => 'director@example.com'],
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Director (MD)',
                'password' => bcrypt('Admin@123'),
                'type' => AdminTypeCast::Director,
                'level' => 1,
                'created_by_admin_id' => $superAdmin->id,
                'profit_share_percent' => 15.00,
                'locale' => 'en',
            ]
        );

        // Create CEO wallet
        $ceo->wallet()->firstOrCreate(
            ['walletable_type' => Admin::class, 'walletable_id' => $ceo->id],
            [
                'uuid' => Str::uuid()->toString(),
                'balance' => 0,
                'currency' => 'INR',
                'status' => 'active',
            ]
        );

        $this->command->info('Director created: director@example.com');




    }
}

