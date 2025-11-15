<?php

namespace Database\Seeders;

use App\Casts\TaxTypeCast;
use App\Models\TaxCode;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelProductCatalogue\Services\ProductManager;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class ProductNewDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxCodes = TaxCode::where('type', TaxTypeCast::GOODS->value)->get();
        if ($taxCodes->isEmpty()) {
            $this->command->warn('No TaxCodes found for goods. Please seed TaxCodes first.');
            return;
        }

        $author = User::firstWhere('email', 'test@example.com');
        if (!$author) {
            $this->command->warn('Default author (test@example.com) not found. Please seed users first.');
            // Optionally create the user if it's essential for the seeder
            // $author = User::factory()->create(['email' => 'test@example.com']);
        }

        $productDataPath = storage_path('app/private/data/products_new');
        $categoryDirectories = File::directories($productDataPath);

        foreach ($categoryDirectories as $categoryDirectory) {
            $categoryUrl = File::basename($categoryDirectory);
            $category = Category::firstWhere('url', $categoryUrl);

            if (!$category) {
                $this->command->line("Skipping category '{$categoryUrl}': Not found in the database.");
                continue;
            }

            $this->command->line("Seeding products for category: {$category->name}");

            $jsonPath = $categoryDirectory . '/products.json';
            if (!File::exists($jsonPath)) {
                $this->command->line("Skipping '{$categoryUrl}': products.json not found.");
                continue;
            }

            $productList = json_decode(File::get($jsonPath));
            $filterGroup = FilterGroup::with('filters.options')->first(); // Simplified for demo, you might want more specific logic

            if (!$filterGroup) {
                $this->command->warn('No FilterGroups found. Please seed FilterGroups first.');
                continue;
            }

            $this->seedProductsForCategory($productList, $filterGroup, $category, $taxCodes, $author);
        }
    }

    protected function seedProductsForCategory($productList, $filterGroup, $parentCategory, $taxCodes, $author)
    {
        foreach ($productList as $productInfo) {
            $hsnTaxCode = $taxCodes->random();

            $productData = Product::factory()->raw([
                'name' => $productInfo->name,
                'url' => $productInfo->url,
                'sku' => $productInfo->sku,
                'price' => fake()->randomElement([12050, 15000, 8000, 45000]),
                'type' => ProductTypeCast::CONFIGURABLE->value,
                'status' => PublishableStatusCast::PUBLISHED->value,
                'filter_group_id' => $filterGroup->id,
                'filter_options' => $this->mapFilterOptions($filterGroup, ProductTypeCast::SIMPLE->value),
                'tax_code_id' => $hsnTaxCode->id,
                'short_description' => $productInfo->short_description,
                'description' => $productInfo->description,
            ]);

            $product = ProductManager::create($productData);

            // Add Media
            $this->attachMediaFiles($product, $parentCategory->url);

            $product->load('variants');
            $product->variants()->each(function ($variant) use ($product, $parentCategory) {
                $this->attachMediaFilesFromParent($product, $variant, $parentCategory->url);
                $variant->update(['status' => PublishableStatusCast::PUBLISHED->value, 'max_quantity' => fake()->numberBetween(3, 12)]);
            });

            // Add Stocks
            $this->addStock($product);

            // Add Category
            $product->categories()->attach([
                $parentCategory->id => [
                    'base_category' => $parentCategory->parent_id ?? $parentCategory->id,
                ],
            ]);

            // Engagement
            if ($author) {
                $author->productEngagements()->create([
                    'product_id' => $product->id,
                    'review' => fake()->text,
                    'rating' => fake()->randomElement([3, 4, 5]),
                    'helpful_votes' => fake()->numberBetween(5, 100),
                ]);
                $author->addToWishlist($product->id);
            }
        }
    }

    protected function attachMediaFiles(Product $product, string $categoryUrl)
    {
        $imagePath = storage_path("app/private/data/products_new/{$categoryUrl}/images/{$product->url}.png");

        if (File::exists($imagePath)) {
            $product->addMedia($imagePath)->preservingOriginal()->toMediaCollection('displayImage');
            $product->addMedia($imagePath)->preservingOriginal()->toMediaCollection('bannerImage');
        } else {
            $this->command->warn("Media file not found for product '{$product->name}': {$imagePath}");
        }
    }

    protected function attachMediaFilesFromParent(Product $parent, Product $product, string $categoryUrl): void
    {
        $imagePath = storage_path("app/private/data/products_new/{$categoryUrl}/images/{$parent->url}.png");

        if (File::exists($imagePath)) {
            $product->addMedia($imagePath)->preservingOriginal()->toMediaCollection('displayImage');
            $product->addMedia($imagePath)->preservingOriginal()->toMediaCollection('bannerImage');
        }
    }

    private function addStock(Product $product): void
    {
        $stockRanges = [[200, 300], [50, 150], [100, 200]];

        foreach ($stockRanges as $range) {
            $product->tiers()->create([
                'init_quantity' => fake()->numberBetween($range[0], $range[1]),
                'sold_quantity' => 0,
                'min_quantity' => 1,
                'max_quantity' => 10,
                'price' => fake()->randomElement([12050, 15000, 8000, 45000]),
            ]);
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

            $selected = $isConfigurable
                ? $options->random(min(2, $options->count()))->pluck('id')->values()->toArray()
                : [$options->random()->id];

            return [(string) $filter->id => array_map('strval', $selected)];
        })->toArray();
    }
}