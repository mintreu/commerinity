<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelProductCatalogue\Services\ProductCreationService;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class MasterDemoProductSeeder extends Seeder
{
    protected $allCategories;
    protected $allFilterGroups;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting demo product seeding...');

        $this->allCategories = Category::with('children')->get();
        $this->allFilterGroups = FilterGroup::with('filters.options')->get();

        $processedCategories = 0;
        $totalProducts = 0;

        foreach ($this->allCategories as $category) {
            $filterGroup = $this->getCorrectFilterGroupOfThisCategory($category);

            if (!is_null($filterGroup)) {
                $productsSeeded = $this->attemptToSeedThisCategory($category, $filterGroup);
                $totalProducts += $productsSeeded;
                $processedCategories++;
            } else {
                $this->command->warn("Filter group not found for category: {$category->url}");
            }
        }

        $this->command->info("Seeding completed! Processed {$processedCategories} categories with {$totalProducts} products.");
    }

    /**
     * Attempt to seed products for a specific category
     */
    protected function attemptToSeedThisCategory(Category $category, FilterGroup $filterGroup): int
    {
        $productList = $this->getFromStorage('private/data/products/demo/' . $category->url . '.json');

        if (!$productList) {
            $this->command->warn("No product data found for category: {$category->url}");
            return 0;
        }

        $this->command->info("Seeding category: {$category->name} ({$category->url}) - " . count($productList) . " products");

        $successCount = 0;
        foreach ($productList as $productInfo) {
            try {
                $this->startSeeding($productInfo, $category, $filterGroup);
                $successCount++;
            } catch (Exception $e) {
                $this->command->error("Failed to seed product {$productInfo->name}: " . $e->getMessage());
                Log::error("Product seeding failed", [
                    'product' => $productInfo->name,
                    'category' => $category->url,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return $successCount;
    }

    /**
     * Seed individual product
     */
    protected function startSeeding($productInfo, Category $category, FilterGroup $filterGroup): void
    {
        // Determine if product should be configurable based on JSON data
        $isConfigurable = $productInfo->configurable ?? false;
        $productType = $isConfigurable ? ProductTypeCast::CONFIGURABLE : ProductTypeCast::SIMPLE;

        $productData = Product::factory()->raw([
            'name' => $productInfo->name,
            'url' => $productInfo->url,
            'sku' => $productInfo->sku,
            'price' => $productInfo->price,
            'type' => $productType,
            'status' => PublishableStatusCast::PUBLISHED->value,
            'filter_group_id' => $filterGroup->id,
            'filter_options' => $this->mapFilterOptions($filterGroup, $productType->value),
        ]);

        $product = ProductCreationService::make($productData)->create();

        // Add Media
        $this->attachMediaFiles($product, $productInfo);

        // Handle variants for configurable products
        if ($isConfigurable) {
            $product->load('variants');
            $product->variants()->each(function ($variant) use ($product) {
                $this->attachMediaFilesFromParent($product, $variant);
                $variant->update([
                    'status' => PublishableStatusCast::PUBLISHED,
                    'max_quantity' => fake()->numberBetween(3, 12)
                ]);
            });
        }

        // Add Stocks
        $this->addStock($product);

        // Add Category mapping with proper base_category handling
        $this->attachCategories($product, $category);
    }

    /**
     * Attach categories with proper base_category handling
     */
    protected function attachCategories(Product $product, Category $category): void
    {
        // Find the root/base category
        $baseCategory = $this->findBaseCategoryId($category);
        $parentCategory = $this->findParentCategory($category);

        $product->categories()->attach([
            $parentCategory->id => [
                'base_category' => $baseCategory,
            ],
        ]);
    }

    /**
     * Find the base category ID (root category) - NEVER returns null
     */
    protected function findBaseCategoryId(Category $category): int
    {
        // Walk up the category tree to find the root
        $current = $category;
        while ($current->parent_id !== null) {
            $parent = $this->allCategories->where('id', $current->parent_id)->first();
            if (!$parent) {
                break; // If parent not found, use current as base
            }
            $current = $parent;
        }
        return $current->id;
    }

    /**
     * Find the appropriate parent category for attachment
     */
    protected function findParentCategory(Category $category): Category
    {
        // If category has a parent, use the parent; otherwise use the category itself
        if ($category->parent_id !== null) {
            $parent = $this->allCategories->where('id', $category->parent_id)->first();
            return $parent ?? $category;
        }

        return $category;
    }

    /**
     * Get media file path
     */
    protected function getMediaFromStorage(string $filename): string
    {
        return storage_path('app/private/media/products/' . $filename);
    }

    /**
     * Attach media files to product
     */
    protected function attachMediaFiles(Product $product, $productInfo): void
    {
        // Use the image names from JSON or fallback to URL-based naming
        $displayImage = $productInfo->displayImage ?? ($product->url . '.jpg');
        $bannerImage = $productInfo->bannerImage ?? ($product->url . '.jpg');

        // Add display image
        $displayImagePath = $this->getMediaFromStorage($displayImage);
        if (file_exists($displayImagePath)) {
            $product->addMedia($displayImagePath)->preservingOriginal()->toMediaCollection('displayImage');
        }

        // Add banner image (only if different from display image)
        if ($displayImage !== $bannerImage) {
            $bannerImagePath = $this->getMediaFromStorage($bannerImage);
            if (file_exists($bannerImagePath)) {
                $product->addMedia($bannerImagePath)->preservingOriginal()->toMediaCollection('bannerImage');
            }
        }
    }

    /**
     * Attach media files from parent to variant
     */
    protected function attachMediaFilesFromParent(Product $parent, Product $variant): void
    {
        $this->copyMediaCollection($parent, $variant, 'displayImage');
        $this->copyMediaCollection($parent, $variant, 'bannerImage');
    }

    /**
     * Copy media collection from parent to child
     */
    protected function copyMediaCollection(Product $parent, Product $child, string $collection): void
    {
        $url = $parent->getFirstMediaUrl($collection);
        $path = $parent->getFirstMediaPath($collection);

        // Try URL first, then fallback to path
        if ($url) {
            try {
                $child->addMediaFromUrl($url)
                    ->preservingOriginal()
                    ->toMediaCollection($collection);
                return;
            } catch (Exception $e) {
                Log::info("Failed to fetch media from URL: {$url}, falling back to path. Error: " . $e->getMessage());
            }
        }

        // Fallback to local path
        if ($path && file_exists($path)) {
            $child->addMedia($path)
                ->preservingOriginal()
                ->toMediaCollection($collection);
        }
    }

    /**
     * Add stock tiers to product
     */
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
                'price' => fake()->randomElement([5000, 10000, 15000, 20000, 25000]), // Prices in paise
            ]);
        }
    }

    /**
     * FIXED: Map filter options based on product type - handles edge cases properly
     */
    private function mapFilterOptions(FilterGroup $filterGroup, string $productType): array
    {
        $isConfigurable = $productType === 'configurable';

        return $filterGroup->filters->mapWithKeys(function ($filter) use ($isConfigurable) {
            $options = $filter->options;

            // If no options available, return empty array for this filter
            if ($options->isEmpty()) {
                return [(string) $filter->id => []];
            }

            $optionsCount = $options->count();

            if ($isConfigurable && $optionsCount > 1) {
                // For configurable products with multiple options, select 2-3 options
                // But never more than available
                $selectedCount = min($optionsCount, max(2, $optionsCount));
                $selected = $options->random($selectedCount);
            } else {
                // For simple products OR configurable with only 1 option, select 1 option
                $selected = $options->random(1);
            }

            // Extract only the IDs and cast to string
            $selectedIds = $selected->pluck('id')->map(function($id) {
                return (string) $id;
            })->toArray();

            return [(string) $filter->id => $selectedIds];
        })->toArray();
    }

    /**
     * Load JSON data from storage
     */
    protected function getFromStorage(string $path)
    {
        $fullPath = storage_path('app/' . $path);

        if (!file_exists($fullPath)) {
            return null;
        }

        $content = file_get_contents($fullPath);
        if (!$content) {
            return null;
        }

        $decoded = json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("Invalid JSON in {$path}: " . json_last_error_msg());
            return null;
        }

        return $decoded;
    }

    /**
     * Get the correct filter group for a category
     */
    protected function getCorrectFilterGroupOfThisCategory(Category $category): ?FilterGroup
    {
        // Comprehensive category to filter group mapping
        $categoryFilterMapping = [
            // Electronics & Tech
            'electronics' => 'Electronics',
            'mobiles-accessories' => 'Electronics',
            'smartphones' => 'Electronics',
            'cases-covers' => 'Electronics',
            'headphones-headsets' => 'Electronics',
            'computers-accessories' => 'Electronics',
            'laptops' => 'Electronics',
            'printers-ink' => 'Electronics',
            'monitors' => 'Electronics',
            'tvs-appliances' => 'Electronics',
            'televisions' => 'Electronics',
            'washing-machines' => 'Electronics',
            'refrigerators' => 'Electronics',
            'car-electronics' => 'Electronics',
            'office-electronics' => 'Electronics',
            'office-supplies' => 'Electronics',
            'business-office' => 'Electronics',
            'industrial-scientific' => 'Electronics',
            'lab-scientific-products' => 'Electronics',
            'janitorial-sanitation-supplies' => 'Electronics',
            'professional-medical-supplies' => 'Electronics',
            'software' => 'Electronics',
            'antivirus-security' => 'Electronics',
            'operating-systems' => 'Electronics',
            'music' => 'Electronics',
            'cds-vinyl' => 'Electronics',
            'digital-music' => 'Electronics',
            'musical-instruments' => 'Electronics',

            // Fashion & Apparel
            'fashion' => 'Apparels',
            'mens-fashion' => 'Apparels',
            'womens-fashion' => 'Apparels',
            'kids-fashion' => 'Apparels',
            'baby-care' => 'Apparels',
            'diapering' => 'Apparels',
            'feeding' => 'Apparels',
            'nursery' => 'Apparels',

            // Home & Furniture
            'home-kitchen' => 'Furniture',
            'furniture' => 'Furniture',
            'cookware-dining' => 'Furniture',
            'home-decor' => 'Furniture',
            'office-furniture' => 'Furniture',

            // Books
            'books' => 'Books',

            // Sports, Toys & Games
            'sports-outdoors' => 'Toys & Games',
            'exercise-fitness' => 'Toys & Games',
            'outdoor-recreation' => 'Toys & Games',
            'team-sports' => 'Toys & Games',
            'toys-games' => 'Toys & Games',
            'action-figures' => 'Toys & Games',
            'puzzles' => 'Toys & Games',
            'building-toys' => 'Toys & Games',
            'automotive' => 'Toys & Games',
            'car-accessories' => 'Toys & Games',
            'motorcycle-parts' => 'Toys & Games',
            'pet-supplies' => 'Toys & Games',
            'dog-supplies' => 'Toys & Games',
            'cat-supplies' => 'Toys & Games',
            'fish-aquatic-pets' => 'Toys & Games',
            'arts-crafts-sewing' => 'Toys & Games',
            'painting-drawing-art-supplies' => 'Toys & Games',
            'sewing' => 'Toys & Games',
            'crafting' => 'Toys & Games',

            // Health & Beauty (Ayurveda)
            'beauty-health' => 'Ayurveda & Herbal Medicines',
            'skin-care' => 'Ayurveda & Herbal Medicines',
            'hair-care' => 'Ayurveda & Herbal Medicines',
            'personal-care' => 'Ayurveda & Herbal Medicines',
            'medicine' => 'Ayurveda & Herbal Medicines',
            'ayurvedic-medicine' => 'Ayurveda & Herbal Medicines',
            'ayurvedic-supplements' => 'Ayurveda & Herbal Medicines',
            'ashwagandha' => 'Ayurveda & Herbal Medicines',
            'turmeric' => 'Ayurveda & Herbal Medicines',
            'triphala' => 'Ayurveda & Herbal Medicines',
            'herbal-remedies' => 'Ayurveda & Herbal Medicines',
            'neem' => 'Ayurveda & Herbal Medicines',
            'amla' => 'Ayurveda & Herbal Medicines',
            'tulsi' => 'Ayurveda & Herbal Medicines',
            'ayurvedic-personal-care' => 'Ayurveda & Herbal Medicines',
            'ayurvedic-hair-care' => 'Ayurveda & Herbal Medicines',
            'ayurvedic-skin-care' => 'Ayurveda & Herbal Medicines',
            'ayurvedic-oral-care' => 'Ayurveda & Herbal Medicines',
            'health' => 'Ayurveda & Herbal Medicines',
            'vitamins-dietary-supplements' => 'Ayurveda & Herbal Medicines',
            'health-care' => 'Ayurveda & Herbal Medicines',
            'medical-equipment' => 'Ayurveda & Herbal Medicines',
            'over-the-counter-medication' => 'Ayurveda & Herbal Medicines',
            'prescription-medication' => 'Ayurveda & Herbal Medicines',
            'first-aid' => 'Ayurveda & Herbal Medicines',

            // Food & Grocery
            'grocery-gourmet-foods' => 'Food Products',
            'snack-foods' => 'Food Products',
            'beverages' => 'Food Products',
            'cooking-baking-supplies' => 'Food Products',
            'spices-and-masalas' => 'Spices & Masala',
            'spices-masalas' => 'Spices & Masala',
        ];

        $filterGroupName = $categoryFilterMapping[$category->url] ?? null;

        if (!$filterGroupName) {
            Log::warning("No filter group mapping found for category: {$category->url}");
            return null;
        }

        return $this->allFilterGroups->where('name', $filterGroupName)->first();
    }
}
