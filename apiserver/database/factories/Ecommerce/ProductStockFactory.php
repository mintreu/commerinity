<?php

declare(strict_types=1);

namespace Database\Factories\Ecommerce;

use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductStock>
 */
class ProductStockFactory extends Factory
{
    protected $model = ProductStock::class;

    public function definition(): array
    {
        $initQuantity = fake()->numberBetween(10, 100);
        $soldQuantity = fake()->numberBetween(0, (int) ($initQuantity * 0.3));
        $landingCost = fake()->numberBetween(10000, 100000); // 100-1000 INR in paise

        return [
            'product_id' => Product::factory(),
            'init_quantity' => $initQuantity,
            'sold_quantity' => $soldQuantity,
            'priority' => fake()->numberBetween(0, 10),
            'low_stock_threshold' => 5,
            'notify_on_low_stock' => true,
            // Purchase Entry Fields
            'landing_cost' => $landingCost,
            // Tracking
            'purchase_invoice_number' => fake()->optional(0.7)->numerify('INV-####'),
            'purchase_date' => fake()->optional(0.8)->dateTimeBetween('-30 days', 'now'),
            'batch_number' => fake()->optional(0.5)->numerify('BATCH-####'),
        ];
    }

    /**
     * Configure as low stock
     */
    public function lowStock(): static
    {
        return $this->state(function (array $attributes) {
            $initQuantity = $attributes['init_quantity'] ?? 100;

            return [
                'sold_quantity' => (int) ($initQuantity * 0.95),
                'low_stock_threshold' => 10,
            ];
        });
    }

    /**
     * Configure as out of stock
     */
    public function outOfStock(): static
    {
        return $this->state(function (array $attributes) {
            $initQuantity = $attributes['init_quantity'] ?? 100;

            return [
                'sold_quantity' => $initQuantity,
            ];
        });
    }

    /**
     * Configure with expiry date
     */
    public function expiring(int $daysUntilExpiry = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => now()->addDays($daysUntilExpiry),
        ]);
    }

    /**
     * Configure as already expired
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    /**
     * Configure with specific supplier
     */
    public function fromSupplier(int $supplierId): static
    {
        return $this->state(fn (array $attributes) => [
            'supplier_id' => $supplierId,
        ]);
    }
}
