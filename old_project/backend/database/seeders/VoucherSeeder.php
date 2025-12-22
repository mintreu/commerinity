<?php

namespace Database\Seeders;

use App\Models\Lifecycle\Level;
use Illuminate\Database\Seeder;
use Mintreu\LaravelCommerinity\Models\Voucher;
use Mintreu\LaravelCommerinity\Models\VoucherCode;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targets = Level::all();
        Voucher::factory(10)
            ->create()
            ->each(function ($voucher) use ($targets) {
                // Add Customer Group
                $voucher->customer_groups()->attach($targets->random());

                // Create Coupons
                $coupons = VoucherCode::factory(5)->create([
                    'starts_from' => $voucher->starts_from,
                    'ends_till' => $voucher->ends_till,
                    'coupon_usage_limit' => $voucher->coupon_usage_limit,
                    'usage_per_customer' => $voucher->usage_per_customer,
                    'voucher_id' => $voucher->id,
                ]);

            });
    }
}
