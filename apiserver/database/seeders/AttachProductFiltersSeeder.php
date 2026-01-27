<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ecommerce\FilterOption;
use App\Models\Ecommerce\Product;
use Illuminate\Database\Seeder;

class AttachProductFiltersSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::purchasable()->whereNull('parent_id')->get();

        foreach ($products as $product) {
            // Get filter options from product's filter group or common ones
            $options = FilterOption::inRandomOrder()->limit(rand(2, 5))->pluck('id')->toArray();

            // Avoid duplicates
            $attached = $product->filterOptions()->pluck('filter_option_id')->toArray();
            $newOptions = array_diff($options, $attached);

            if (!empty($newOptions)) {
                $product->filterOptions()->attach(array_slice($newOptions, 0, 3));
            }
        }

        echo "Attached filters to " . $products->count() . " products\n";
    }
}
