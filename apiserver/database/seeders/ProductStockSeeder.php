<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\AddressTypeCast;
use App\Models\Address;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds stock records for all products
 *
 * Creates realistic stock entries with:
 * - Random quantities between 50-500 units
 * - Proper landing cost based on product price
 * - Affiliate points (BV, PV, reward points)
 * - FIFO priority ordering
 */
class ProductStockSeeder extends Seeder
{


    private array $cities = [
        ['city' => 'Mumbai', 'state' => 'MH', 'postal' => '400001'],
        ['city' => 'Delhi', 'state' => 'DL', 'postal' => '110001'],
        ['city' => 'Bangalore', 'state' => 'KA', 'postal' => '560001'],
        ['city' => 'Chennai', 'state' => 'TN', 'postal' => '600001'],
        ['city' => 'Kolkata', 'state' => 'WB', 'postal' => '700001'],
        ['city' => 'Hyderabad', 'state' => 'TG', 'postal' => '500001'],
        ['city' => 'Pune', 'state' => 'MH', 'postal' => '411001'],
        ['city' => 'Ahmedabad', 'state' => 'GJ', 'postal' => '380001'],
        ['city' => 'Jaipur', 'state' => 'RJ', 'postal' => '302001'],
        ['city' => 'Lucknow', 'state' => 'UP', 'postal' => '226001'],
    ];




    public function run(): void
    {
        $this->command->info('🚀 Starting Product Stock Seeding...');




//        $products = Product::whereNull('parent_id')->get();

        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Run ProductSeeder first.');

            return;
        }

        $seeded = 0;

        foreach ($products as $product) {
            // Skip if stock already exists
            if ($product->stocks()->exists()) {
                $this->command->info("  ⏭ Skipping {$product->name} (stock exists)");

                continue;
            }

            $this->createStockForProduct($product);
            $seeded++;
            $this->command->info("  ✓ {$product->name}");
        }

        $this->command->info("\n✅ Seeded stock for {$seeded} products");
    }

    protected function createStockForProduct(Product $product): void
    {
        // Calculate realistic values
      //  $productPrice = $product->price; // in paise
        $productPrice = fake()->randomElement([20000,35000,45000]);
        $landingCost = (int) ($productPrice * 0.6); // 60% of price (40% margin)
        $profitMargin = 40.00;

        // Affiliate calculations
        $profit = $productPrice - $landingCost;
        $bv = (int) ($profit * 0.10); // 10% of profit as BV
        $pv = (int) ($profit * 0.05); // 5% of profit as PV
        $rewardPoints = (int) floor($profit / 100); // 1 point per rupee

        // Random stock quantity (50-500)
        $quantity = random_int(50, 500);

        $stock = ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => $quantity,
            'sold_quantity' => 0,
            'priority' => 1,
            'landing_cost' => $landingCost,
            'low_stock_threshold' => 10,
            'notify_on_low_stock' => true,
            'batch_number' => 'BATCH-'.strtoupper(substr(md5((string) $product->id), 0, 8)),
            'purchase_date' => now()->subDays(random_int(1, 30)),
            'expiry_date' => now()->addMonths(random_int(6, 24)),
            'notes' => 'Initial stock entry',
        ]);

        $city = $this->cities[array_rand($this->cities)];

        $pickupAddress = Address::create([
            'uuid' => Str::uuid()->toString(),
            'addressable_type' => get_class($stock),
            'addressable_id' => $stock->id,
            'type' => 'home',
            'person_name' => fake()->name,
            'person_mobile' => fake()->numerify('##########'),
            'address_1' => rand(1, 999).', '.['MG Road', 'Station Road', 'Main Street', 'Park Avenue', 'Gandhi Nagar'][array_rand(['MG Road', 'Station Road', 'Main Street', 'Park Avenue', 'Gandhi Nagar'])],
            'address_2' => ['Near Bus Stand', 'Opposite Mall', 'Behind Temple', 'Next to School', null][array_rand(['Near Bus Stand', 'Opposite Mall', 'Behind Temple', 'Next to School', null])],
            'city' => $city['city'],
            'postal_code' => $city['postal'],
            'state_code' => $city['state'],
            'country_code' => 'IN',
            'default' => true,
        ]);

        $stock->update(['address_id' => $pickupAddress->id]);

        $product->update([
            'price' => $productPrice,
            'bv' => $bv,
            'pv' => $pv,
            'reward_points' => $rewardPoints,
            'min_quantity' => 1,
            'max_quantity' => 50,
            'wholesale_unit_quantity' => null,
            'is_commissionable' => true,
            'commission_rate' => 5.00,
        ]);
    }
}

