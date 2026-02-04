<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\PaymentMethodCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seed demo transactions for testing.
 */
class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding demo transactions...');

        $users = User::with('wallet')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Run DemoUserSeeder first.');

            return;
        }

        $transactionTypes = [
            // Credits
            [
                'type' => TransactionTypeCast::CREDIT,
                'purpose' => 'commission',
                'description' => 'Referral commission',
                'amount_range' => [5000, 50000], // 50-500 INR
                'payment_method' => PaymentMethodCast::WALLET,
            ],
            [
                'type' => TransactionTypeCast::CREDIT,
                'purpose' => 'bonus',
                'description' => 'Level bonus',
                'amount_range' => [10000, 100000], // 100-1000 INR
                'payment_method' => PaymentMethodCast::WALLET,
            ],
            [
                'type' => TransactionTypeCast::CREDIT,
                'purpose' => 'deposit',
                'description' => 'Wallet recharge',
                'amount_range' => [100000, 500000], // 1000-5000 INR
                'payment_method' => PaymentMethodCast::WALLET,
            ],
            [
                'type' => TransactionTypeCast::CREDIT,
                'purpose' => 'refund',
                'description' => 'Order refund',
                'amount_range' => [20000, 100000], // 200-1000 INR
                'payment_method' => PaymentMethodCast::WALLET,
            ],

            // Debits
            [
                'type' => TransactionTypeCast::DEBIT,
                'purpose' => 'purchase',
                'description' => 'Product purchase',
                'amount_range' => [50000, 200000], // 500-2000 INR
                'payment_method' => PaymentMethodCast::WALLET,
            ],
            [
                'type' => TransactionTypeCast::DEBIT,
                'purpose' => 'withdrawal',
                'description' => 'Bank withdrawal',
                'amount_range' => [100000, 500000], // 1000-5000 INR
                'payment_method' => PaymentMethodCast::PAYOUT_BANK,
            ],
            [
                'type' => TransactionTypeCast::DEBIT,
                'purpose' => 'subscription',
                'description' => 'Membership renewal',
                'amount_range' => [50000, 150000], // 500-1500 INR
                'payment_method' => PaymentMethodCast::WALLET,
            ],
            [
                'type' => TransactionTypeCast::DEBIT,
                'purpose' => 'fee',
                'description' => 'Job application fee',
                'amount_range' => [29900, 99900], // 299-999 INR
                'payment_method' => PaymentMethodCast::WALLET,
            ],
        ];

        $statuses = [
            TransactionStatusCast::COMPLETED,
            TransactionStatusCast::COMPLETED,
            TransactionStatusCast::COMPLETED,
            TransactionStatusCast::PENDING,
            TransactionStatusCast::FAILED,
        ];

        $totalCreated = 0;

        foreach ($users as $user) {
            $wallet = $user->wallet;
            if (! $wallet) {
                $wallet = Wallet::create([
                    'walletable_type' => $user->getMorphClass(),
                    'walletable_id' => $user->getKey(),
                    'balance' => 0,
                    'currency' => 'INR',
                    'status' => 'active',
                ]);
            }

            $currentBalance = $wallet->balance;

            // Seed 4 years (48 months) of data with monthly spread
            for ($monthsBack = 0; $monthsBack < 48; $monthsBack++) {
                $transactionsThisMonth = rand(2, 6);
                $baseDate = now()->subMonths($monthsBack)->startOfMonth()->addDays(10);

                for ($i = 0; $i < $transactionsThisMonth; $i++) {
                    $txnType = $transactionTypes[array_rand($transactionTypes)];
                    $amount = rand($txnType['amount_range'][0], $txnType['amount_range'][1]);
                    $status = $statuses[array_rand($statuses)];
                    $fee = $txnType['purpose'] === 'withdrawal' ? (int) ($amount * 0.02) : 0;

                    $balanceAfter = $txnType['type'] === TransactionTypeCast::CREDIT
                        ? $currentBalance + $amount
                        : max(0, $currentBalance - $amount - $fee);

                    $isVerified = $status === TransactionStatusCast::COMPLETED;
                    $createdAt = $baseDate->copy()->addMinutes(rand(0, 60 * 24 * 5));

                    Transaction::create([
                        'uuid' => 'TXN-'.Str::upper(Str::random(12)),
                        'wallet_id' => $wallet->id,
                        'transactionable_type' => Wallet::class,
                        'transactionable_id' => $wallet->id,
                        'type' => $txnType['type'],
                        'purpose' => $txnType['purpose'],
                        'amount' => $amount,
                        'fee' => $fee,
                        'tax' => 0,
                        'balance_after' => $balanceAfter,
                        'currency' => 'INR',
                        'payment_method' => $txnType['payment_method'],
                        'description' => $txnType['description'],
                        'status' => $status,
                        'verified' => $isVerified,
                        'verified_at' => $isVerified ? $createdAt->copy()->addMinutes(rand(5, 120)) : null,
                        'metadata' => [
                            'source' => 'demo_seeder',
                            'provider' => 'native',
                        ],
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    $totalCreated++;

                    if ($status === TransactionStatusCast::COMPLETED) {
                        $currentBalance = $balanceAfter;
                    }
                }
            }
        }

        $this->command->info("Seeded {$totalCreated} demo transactions.");
    }
}
