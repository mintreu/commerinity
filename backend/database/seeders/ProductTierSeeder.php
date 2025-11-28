<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductSupplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Mintreu\LaravelGeokit\Casts\AddressTypeCast;
use Mintreu\LaravelGeokit\Models\Address;

class ProductTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        $products->each(function ($product){
           $this->addStock($product);
        });




    }



    private function addStock(\Mintreu\LaravelProductCatalogue\Models\Product $product): void
    {
        $stockRanges = [
            [200, 300],
            [50, 150],
            [100, 200],
        ];

        foreach ($stockRanges as $range) {

            $address = Address::factory()->create([
                'title' => fake()->word.' Pickup Address',
                'type'  => AddressTypeCast::PICKUP->value
            ]);
            $supplier = ProductSupplier::factory()->create([
                'name' => fake()->company.' Supplier',
            ]);

            $product->tiers()->create([
                'init_quantity' => fake()->numberBetween($range[0], $range[1]),
                'sold_quantity' => 0,
                'min_quantity' => 1,
                'max_quantity' => 10,

                'purchase_invoice_id' => Str::random(8),

                'landing_cost' => $landing = fake()->randomElement([12050,15000,8000,45000]),
                'profit_margin' => $margin = fake()->randomElement([5,15,20,10]),  // tax %
                'price' => $landing + ($landing * $margin /100),
                'address_id' => $address->id,
                'product_supplier_id' => $supplier->id
            ]);
        }
    }


}
