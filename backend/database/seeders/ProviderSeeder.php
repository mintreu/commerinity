<?php

namespace Database\Seeders;

use App\Casts\ProviderTypeCast;
use App\Models\Provider;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $razorpay =  Provider::create([
            'name' => 'Razorpay',
            'url' => 'razorpay-payment',
            'desc' => 'Payment Gateway of Application',
            'type' => ProviderTypeCast::PAYMENT,
            'key' => '',
            'secret' => '',
            'status' => true,
            'default' => true,
        ]);

        $payyou =  Provider::create([
            'name' => 'PayYou',
            'url' => 'pay-you-payment',
            'desc' => 'Payment Gateway of Application',
            'type' => ProviderTypeCast::PAYMENT,
            'key' => '',
            'secret' => '',
            'status' => true,
            'default' => false,
        ]);
    }
}
