<?php

declare(strict_types=1);

namespace Database\Factories\Ecommerce;

use App\Casts\SaleActionTypeCast;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\Sale;
use App\Models\Ecommerce\SaleProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleProduct>
 */
class SaleProductFactory extends Factory
{
    protected $model = SaleProduct::class;

    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'starts_from' => now()->subDay(),
            'ends_till' => now()->addDay(),
            'action_type' => SaleActionTypeCast::BY_FIXED->value,
            'sale_price' => fake()->numberBetween(5000, 20000),
            'discount_amount' => 0,
            'end_other_rules' => false,
            'sort_order' => 0,
            'target_type' => null,
            'target_id' => null,
        ];
    }
}
