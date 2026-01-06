<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * BeneficiarySeeder - Seed test beneficiary for payout testing
 */
final class BeneficiarySeeder extends Seeder
{
    /**
     * Run the seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Get or create test user
            $user = User::first();

            if (! $user) {
                $user = \App\Models\User::factory()->create([
                    'name' => 'Test User',
                    'email' => 'test@mintreu.com',
                    'mobile' => '9999999999',
                ]);
            }

            // Get or create wallet
            $wallet = Wallet::firstOrCreate([
                'walletable_type' => \App\Models\User::class,
                'walletable_id' => $user->id,
            ]);

            // Check if test beneficiary exists
            $existingBeneficiary = $wallet->beneficiaryAccounts()
                ->where('holder_name', 'Test User')
                ->first();

            if (! $existingBeneficiary) {
                // Create test beneficiary (savings account)
                $wallet->beneficiaryAccounts()->create([
                    'type' => 'savings',
                    'holder_name' => 'Test User',
                    'account_number' => '1234567890123456',
                    'ifsc_code' => 'SBIN0001234',
                    'bank_name' => 'Test Bank',
                    'status' => 'verified',
                    'is_default' => true,
                    'provider_beneficiary_id' => 'TEST-BENE-001', // Simulate Cashfree beneficiary ID
                ]);

                echo "✅ Created test beneficiary for user ID: {$user->id}\n";
            } else {
                echo "ℹ️ Test beneficiary already exists for user ID: {$user->id}\n";
            }
        });

        echo "✅ Beneficiary seeder completed\n";
    }
}
