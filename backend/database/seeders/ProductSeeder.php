<?php

namespace Database\Seeders;

use App\Casts\ProductTypeCast;
use App\Models\Category;
use App\Models\FilterGroup;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allProductCategory = $this->fetchAllProductCategories();

        if ($allProductCategory->isEmpty()) {
            $this->command->info('No categories of type PRODUCT found. Seeder aborted.');
            return;
        }



        foreach ($allProductCategory as $productCategory) {
            $this->createProducts($productCategory);
        }

        $this->command->info('Product seeding completed successfully!');
    }

    private function fetchAllProductCategories()
    {
        return Category::with('children')
            //->where('type', CategoryTypeCast::PRODUCT->value)
            ->where('parent_id', null)
            ->get();
    }

    private function createProducts($productCategory)
    {
        Product::factory(20)
            ->hasVariants(4)
            ->create([
                'type' => ProductTypeCast::CONFIGURABLE
            ])
            ->each(function (Product $product) use ($productCategory) {
                $this->processProduct($product, $productCategory);
            });
    }

    private function processProduct(Product $product, $productCategory)
    {
        $product->load('variants');

        $this->addStock($product);
        $this->attachCoreCategory($product, $productCategory);
        $this->attachChildrenCategories($product, $productCategory);
        $this->attachFilterOptions($product, $productCategory);

        $product->save();
    }

    private function addStock(Product $product)
    {
        $stocks = [
            [200, 300],
            [50, 150],
            [100, 200],
        ];

        foreach ($stocks as $stockRange) {
            $stock = $product->tiers()->create([
                'init_quantity' => fake()->randomElement($stockRange),
                'sold_quantity' => 0,
                'min_quantity' => 1,
                'max_quantity' => 10,
                'price' => fake()->randomElement([50,100,150,200]),
            ]);

//
//            $stockAddress = Address::factory()->create([
//                'type' => AddressTypeCast::PICKUP,
//                'default' => true
//            ]);
//
//            $stock->update([
//                'address_id' => $stockAddress?->id,
//            ]);
        }
    }

    private function attachCoreCategory(Product $product, $productCategory)
    {
        $product->update([
            'category_id' => $productCategory->id,
            //'product_brand_id' => $allBrands->random(2)->first()->id
        ]);
    }

    private function attachChildrenCategories(Product $product, $productCategory)
    {
        if ($productCategory->children->count()) {
            $product->categories()->attach(
                $productCategory->children->random(2)->pluck('id')->toArray()
            );
        }
    }

    private function attachFilterOptions(Product $product, $productCategory)
    {
        $selectedFilterGroup = FilterGroup::with([
            'filters' => function ($query) {
                $query->with(['options']);
            }
        ])->firstWhere('id', $product->filter_group_id);

        if ($selectedFilterGroup) {
            foreach ($product->variants as $variant) {
//                $optionIds = $selectedFilterGroup->filters
//                    ->map(fn($filter) => $filter->options->isNotEmpty() ? $filter->options->random()->id : null)
//                    ->filter()
//                    ->toArray();
//
//
//
//                if (!empty($optionIds)) {
//                    $variant->filterOptions()->sync($optionIds,['filter_id' => $filterId]);
//                }

                $optionIdWithPivot = [];

                foreach ($selectedFilterGroup->filters as $filter) {
                    if ($filter->options->isNotEmpty()) {
                        $option = $filter->options->random();
                        $optionIdWithPivot[$option->id] = ['filter_id' => $filter->id];
                    }
                }

                if (!empty($optionIdWithPivot)) {
                    $variant->filterOptions()->sync($optionIdWithPivot);
                }


                $variant->update([
                    'category_id' => $productCategory->id,
                   // 'product_brand_id' => $allBrands->random(2)->first()->id
                ]);
                $this->attachChildrenCategories($variant, $productCategory);




                $this->addStock($variant);

            }
        }
    }
}
