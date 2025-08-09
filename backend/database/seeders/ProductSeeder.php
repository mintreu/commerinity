<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;

use Mintreu\LaravelProductCatalogue\Models\FilterGroup;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelProductCatalogue\Services\ProductCreationService;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::with('children', 'descendants')
            ->whereNull('parent_id')
            ->get();

        $filterGroups = FilterGroup::with('filters.options')->get();

        foreach ($categories as $category) {
            $this->createProductsForCategory($category, $filterGroups);
        }
    }

    private function createProductsForCategory(Category $category, $filterGroups): void
    {
        $products = [];

        for ($i = 0; $i < 15; $i++) {
            $filterGroup = $filterGroups->random();
            $type = fake()->randomElement(array_column(ProductTypeCast::cases(), 'value'));

            $productData = Product::factory()->raw([
                'type' => ProductTypeCast::CONFIGURABLE,
                'status' => PublishableStatusCast::PUBLISHED->value,
                'filter_group_id' => $filterGroup->id,
                'filter_options' => $this->mapFilterOptions($filterGroup, $type),
            ]);

            $product = ProductCreationService::make($productData)->create();
            $products[] = $product;
        }

        foreach ($products as $product) {
            $this->processProduct($product, $category);
        }
    }


    private function mapFilterOptions($filterGroup, string $productType): array
    {
        $isConfigurable = $productType === 'configurable';

        return $filterGroup->filters->mapWithKeys(function ($filter) use ($isConfigurable) {
            $options = $filter->options;

            if ($options->isEmpty()) {
                return [(string) $filter->id => []];
            }

            if ($isConfigurable) {
                $selected = $options->random(min(2, $options->count()))->pluck('id')->values()->toArray();
            } else {
                $selected = [$options->random()->id];
            }

            // Always cast to string to match form data format
            $selected = array_map('strval', $selected);

            return [(string) $filter->id => $selected];
        })->toArray();
    }



    private function processProduct(Product $product, Category $category): void
    {
        $product->load('variants');

        $this->addStock($product);
        $this->attachCoreCategory($product, $category);
        $this->attachChildCategories($product, $category);

        $product->save();
    }

    private function addStock(Product $product): void
    {
        $stockRanges = [
            [200, 300],
            [50, 150],
            [100, 200],
        ];

        foreach ($stockRanges as $range) {
            $product->tiers()->create([
                'init_quantity' => fake()->numberBetween($range[0], $range[1]),
                'sold_quantity' => 0,
                'min_quantity' => 1,
                'max_quantity' => 10,
                'price' => fake()->randomElement([50, 100, 150, 200]),
            ]);
        }
    }

    private function attachCoreCategory(Product $product, Category $category): void
    {
        $product->categories()->attach($category->id, [
            'base_category' => true,
        ]);
    }

    private function attachChildCategories(Product $product, Category $category): void
    {
        $childCategories = $category->descendants->isNotEmpty()
            ? $category->descendants
            : $category->children;

        if ($childCategories->isNotEmpty()) {
            $product->categories()->attach($childCategories->pluck('id')->toArray());
        }
    }
}
