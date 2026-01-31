<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use Illuminate\Database\Seeder;

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
    public function run(): void
    {
        $this->command->info('🚀 Starting Product Stock Seeding...');

        $products = Product::whereNull('parent_id')->get();

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

        ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => $quantity,
            'sold_quantity' => 0,
            'priority' => 1,
            'landing_cost' => $landingCost,
            'profit_margin' => $profitMargin,
            'price' => null, // Use product price
            'min_quantity' => 1,
            'max_quantity' => 50,
            'wholesale_unit_quantity' => null,
            'bv' => $bv,
            'pv' => $pv,
            'reward_points' => $rewardPoints,
            'commission_rate' => 5.00,
            'is_commissionable' => true,
            'low_stock_threshold' => 10,
            'notify_on_low_stock' => true,
            'batch_number' => 'BATCH-'.strtoupper(substr(md5((string) $product->id), 0, 8)),
            'purchase_date' => now()->subDays(random_int(1, 30)),
            'expiry_date' => now()->addMonths(random_int(6, 24)),
            'notes' => 'Initial stock entry',
        ]);

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
