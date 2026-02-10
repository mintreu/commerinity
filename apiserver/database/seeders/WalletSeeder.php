<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\WalletStatusCast;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed wallets for demo users with realistic balances.
 */
class WalletSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding demo user wallets...');

        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Run DemoUserSeeder first.');

            return;
        }

        // Different balance ranges based on user type
        $balanceRanges = [
            'regular' => ['min' => 0, 'max' => 50000],        // 0-500 INR
            'member' => ['min' => 100000, 'max' => 500000],   // 1000-5000 INR
            'promoter' => ['min' => 500000, 'max' => 2000000], // 5000-20000 INR
            'advisor' => ['min' => 1000000, 'max' => 5000000], // 10000-50000 INR
            'mentor' => ['min' => 2000000, 'max' => 10000000], // 20000-100000 INR
        ];

        foreach ($users as $user) {
            // Skip if wallet already exists
            if ($user->wallet()->exists()) {
                continue;
            }

            $userType = $user->type->value ?? 'regular';
            $range = $balanceRanges[$userType] ?? $balanceRanges['regular'];

            $balance = rand($range['min'], $range['max']);
            $totalCredited = $balance + rand(100000, 500000); // Add some extra for history
            $totalDebited = $totalCredited - $balance;

            $wallet = new Wallet([
                'balance' => $balance,
                'hold_balance' => 0,
                'total_credited' => $totalCredited,
                'total_debited' => $totalDebited,
                'points' => rand(0, 1000),
                'pin' => Hash::make('123456'), // Demo PIN (matches 6-digit validation)
                'pin_updated_at' => now()->subDays(rand(1, 30)),
                'currency' => 'INR',
                'status' => WalletStatusCast::ACTIVE,
            ]);

            $user->wallet()->save($wallet);
        }

        $this->command->info('Seeded '.Wallet::count().' wallets for demo users.');
    }
}

