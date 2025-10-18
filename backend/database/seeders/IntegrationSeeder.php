<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Mintreu\LaravelIntegration\Models\Integration;

class IntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $razorpay =  Integration::create([
            'name' => 'Razorpay',
            'url' => 'razorpay-payment',
            'desc' => 'Payment Gateway of Application',
            'type' => IntegrationTypeCast::PAYMENT,
            'key'      => config('laravel-integration.providers.payment.razorpay.key'),
            'secret'   => config('laravel-integration.providers.payment.razorpay.secret'),
            'logo_url'  => 'https://badges.razorpay.com/badge-light.png',
            'status' => true,
            'default' => false,
        ]);

        $razorpayPayout =  Integration::create([
            'name' => 'Razorpay (Payout)',
            'url' => 'razorpay-payout',
            'desc' => 'Payout Gateway of Application',
            'type' => IntegrationTypeCast::PAYOUT,
            'key'      => env('RAZORPAY_PAYOUT_KEY', ''),
            'secret'   => env('RAZORPAY_PAYOUT_SECRET', ''),
            'logo_url'  => 'https://badges.razorpay.com/badge-light.png',
            'status' => true,
            'default' => true,
        ]);

        $Cashfree =  Integration::create([
            'name' => 'CashFree',
            'url' => 'cash-free-payment',
            'desc' => 'Payment Gateway of Application',
            'type' => IntegrationTypeCast::PAYMENT,
            'key'      => config('laravel-integration.providers.payment.cash-free.key'),
            'secret'   => config('laravel-integration.providers.payment.cash-free.secret'),
            'status' => true,
            'default' => true,
        ]);

        $cod =  Integration::create([
            'name' => 'COD',
            'url' => 'cash-payment',
            'desc' => 'Cash Payment Gateway(COD)',
            'type' => IntegrationTypeCast::PAYMENT,
            'key'      => null,
            'secret'   => null,
            'logo_url'  => 'https://badges.razorpay.com/badge-light.png',
            'status' => true,
            'default' => false,
        ]);

        $wallet =  Integration::create([
            'name' => 'Wallet',
            'url' => 'wallet-payment',
            'desc' => 'Wallet Payment Gateway',
            'type' => IntegrationTypeCast::PAYMENT,
            'key'      => null,
            'secret'   => null,
            'logo_url'  => '#',
            'status' => true,
            'default' => false,
        ]);



        $fast2Sms =  Integration::create([
            'name' => 'Fast2Sms',
            'url' => 'fast2sms-sms',
            'desc' => 'Sms Gateway of Application',
            'type' => IntegrationTypeCast::SMS,
            'key' => '',
            'secret' => '',
            'status' => false,
            'default' => true,
        ]);






    }
}
