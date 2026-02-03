<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\IntegrationTypeCast;
use App\Models\Integration;
use Illuminate\Database\Seeder;

class IntegrationSeeder extends Seeder
{
    /**
     * Seed payment/payout integrations
     */
    public function run(): void
    {



        // Cashfree Payment (Default)
        Integration::firstOrCreate(
            ['slug' => 'cashfree'],
            [
                'name' => 'Cashfree',
                'type' => IntegrationTypeCast::PAYMENT,
                'credentials' => [
                    'key' => config('services.payment.cashfree.key'),
                    'secret' => config('services.payment.cashfree.secret'),
                ],
                'settings' => [],
                'is_sandbox' => config('services.payment.cashfree.sandbox', true),
                'is_active' => true,
                'is_default' => true,
            ]
        );

        // Cashfree Payout (Default)
        Integration::firstOrCreate(
            ['slug' => 'cashfree-payout'],
            [
                'name' => 'Cashfree Payout',
                'type' => IntegrationTypeCast::PAYOUT,
                'credentials' => [
                    'key' => config('services.payout.cashfree.key'),
                    'secret' => config('services.payout.cashfree.secret'),
                ],
                'settings' => [],
                'is_sandbox' => config('services.payout.cashfree.sandbox', true),
                'is_active' => true,
                'is_default' => true,
            ]
        );

        // Razorpay Payment (Backup)
        Integration::firstOrCreate(
            ['slug' => 'razorpay'],
            [
                'name' => 'Razorpay',
                'type' => IntegrationTypeCast::PAYMENT,
                'credentials' => [
                    'api_key' => config('services.razorpay.key'),
                    'api_secret' => config('services.razorpay.secret'),
                ],
                'settings' => [],
                'is_sandbox' => false,
                'is_active' => false, // Not active by default
                'is_default' => false,
            ]
        );

        // Razorpay Payout (Backup)
        Integration::firstOrCreate(
            ['slug' => 'razorpay-payout'],
            [
                'name' => 'Razorpay Payout',
                'type' => IntegrationTypeCast::PAYOUT,
                'credentials' => [
                    'api_key' => env('RAZORPAY_PAYOUT_KEY'),
                    'api_secret' => env('RAZORPAY_PAYOUT_SECRET'),
                ],
                'settings' => [],
                'is_sandbox' => false,
                'is_active' => false, // Not active by default
                'is_default' => false,
            ]
        );

        // Native Payment (Cash/COD)
        Integration::firstOrCreate(
            ['slug' => 'native'],
            [
                'name' => 'Native Payment',
                'type' => IntegrationTypeCast::PAYMENT,
                'credentials' => [],
                'settings' => [],
                'is_sandbox' => false,
                'is_active' => true,
                'is_default' => false,
            ]
        );

        // Native Payout (Wallet)
        Integration::firstOrCreate(
            ['slug' => 'native-payout'],
            [
                'name' => 'Native Payout',
                'type' => IntegrationTypeCast::PAYOUT,
                'credentials' => [],
                'settings' => [],
                'is_sandbox' => false,
                'is_active' => true,
                'is_default' => false,
            ]
        );
    }
}
