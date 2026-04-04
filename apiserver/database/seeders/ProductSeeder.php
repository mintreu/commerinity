<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\FilterGroup;
use App\Models\Ecommerce\Product;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    private array $categoryFilterMap = [
        'spices-masalas' => 'Spices & Masala',
        'ayurvedic-hair-care' => 'Ayurveda & Herbal Medicines',
        'ayurvedic-oral-care' => 'Ayurveda & Herbal Medicines',
        'mens-fashion' => 'Apparels',
        'cases-covers' => 'Electronics',
    ];

    private array $categoriesToSeed = [
        'spices-masalas',
        'ayurvedic-hair-care',
        'ayurvedic-oral-care',
        'mens-fashion',
        'cases-covers',
    ];

    /**
     * Local/legacy catalog entries that should never be seeded.
     *
     * @var array<int, string>
     */
    private array $excludedProductUrls = [
        'pani-puri-masala',
    ];

    public function run(): void
    {
        $this->command->info('🚀 Starting Master Product Seeding...');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $totalSeeded = 0;
        $totalFailed = 0;

        foreach ($this->categoriesToSeed as $categoryUrl) {
            $this->command->info("\n📦 Processing category: {$categoryUrl}");

            try {
                $count = $this->seedCategoryProducts($categoryUrl);
                $totalSeeded += $count;
                $this->command->info("✓ Successfully seeded {$count} products for {$categoryUrl}");
            } catch (Exception $e) {
                $totalFailed++;
                $this->command->error("✗ Failed to seed category {$categoryUrl}: {$e->getMessage()}");
            }
        }

        $this->command->info("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info('🎉 Seeding Complete!');
        $this->command->info("   Total Products Seeded: {$totalSeeded}");
        $this->command->info("   Failed Categories: {$totalFailed}");
    }

    protected function seedCategoryProducts(string $categoryUrl): int
    {
        $category = Category::where('url', $categoryUrl)->first();
        if (! $category) {
            throw new Exception("Category '{$categoryUrl}' not found in database");
        }

        $filterGroupName = $this->categoryFilterMap[$categoryUrl] ?? null;
        if (! $filterGroupName) {
            throw new Exception("No filter group mapping found for category '{$categoryUrl}'");
        }

        $filterGroup = FilterGroup::where('name', $filterGroupName)->first();
        if (! $filterGroup) {
            throw new Exception("Filter group '{$filterGroupName}' not found in database");
        }

        $jsonPath = "private/data/products/{$categoryUrl}/{$categoryUrl}.json";
        $products = $this->getFromStorage($jsonPath);

        if (empty($products)) {
            throw new Exception("No products found in JSON file: {$jsonPath}");
        }

        $seededCount = 0;
        foreach ($products as $productData) {
            if (in_array((string) ($productData->url ?? ''), $this->excludedProductUrls, true)) {
                continue;
            }

            try {
                $product = $this->createProduct($productData, $category, $filterGroup);
                $this->assignProductCategories($product, $category, $productData);
                $this->attachMediaFiles($product, $category);
                $seededCount++;
                $this->command->info("  ✓ {$product->name}");
            } catch (Exception $e) {
                $this->command->error("  ✗ Failed: {$productData->name} - {$e->getMessage()}");
            }
        }

        return $seededCount;
    }

    protected function createProduct(object $productData, Category $category, FilterGroup $filterGroup): Product
    {
        $type = isset($productData->configurable) && $productData->configurable
            ? ProductTypeCast::CONFIGURABLE->value
            : ProductTypeCast::SIMPLE->value;

        $seed = abs(crc32((string) $productData->sku));
        $weightGrams = 100 + ($seed % 2000);
        $lengthCm = 5 + ($seed % 50);
        $widthCm = 5 + ($seed % 40);
        $heightCm = 2 + ($seed % 30);

        return Product::updateOrCreate(
            ['sku' => $productData->sku],
            [
                'name' => $productData->name,
                'url' => $productData->url,
                'status' => ProductStatusCast::PUBLISHED->value,
                'type' => $type,
                'price' => isset($productData->price) ? (int) round($productData->price) : 0, // data already stores paise
                'bv' => 0,
                'pv' => 0,
                'reward_points' => 0,
                'min_quantity' => 1,
                'max_quantity' => 50,
                'wholesale_unit_quantity' => null,
                'weight_grams' => $weightGrams,
                'length_cm' => $lengthCm,
                'width_cm' => $widthCm,
                'height_cm' => $heightCm,
                'is_commissionable' => true,
                'commission_rate' => null,
                'short_description' => $productData->short_description ?? null,
                'description' => $productData->description ?? null,
                'category_id' => $category->id,
                'filter_group_id' => $filterGroup->id,
                'is_returnable' => true,
                'return_days' => 7,
                'view_count' => 0,
            ]
        );
    }

    protected function attachMediaFiles(Product $product, Category $category): void
    {
        try {
            $dir = $category->url.'/'.$product->url.'/';
            $displayImagePath = Storage::path('data/products/'.$dir.$product->url.'.png');

            if (file_exists($displayImagePath)) {
                $product->clearMediaCollection('displayImage');
                $product->addMedia($displayImagePath)
                    ->preservingOriginal()
                    ->toMediaCollection('displayImage');
            }

            $allImages = Storage::disk('local')->allFiles('data/products/'.$dir);

            foreach ($allImages as $image) {
                $imagePath = Storage::path($image);
                if (file_exists($imagePath)) {
                    $alreadyAdded = $product->getMedia('bannerImage')
                        ->contains(fn ($media) => $media->file_name === basename($imagePath));

                    if (! $alreadyAdded) {
                        $product->addMedia($imagePath)
                            ->preservingOriginal()
                            ->toMediaCollection('bannerImage');
                    }
                }
            }

            if ($product->type == ProductTypeCast::CONFIGURABLE->value) {
                $variants = $product->variants()->get();
                foreach ($variants as $variant) {
                    $variant->clearMediaCollection('displayImage');
                    $variant->addMedia($displayImagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('displayImage');


                    foreach ($allImages as $image) {
                        $imagePath = Storage::path($image);
                        if (file_exists($imagePath)) {
                            $alreadyAdded = $product->getMedia('bannerImage')
                                ->contains(fn ($media) => $media->file_name === basename($imagePath));

                            if (! $alreadyAdded) {
                                $product->addMedia($imagePath)
                                    ->preservingOriginal()
                                    ->toMediaCollection('bannerImage');
                            }
                        }
                    }

                }
            }




            // |||||||||||||||||||||||||||||||||


        } catch (\Throwable $e) {
            $product->status = ProductStatusCast::DRAFT->value;
            $product->save();
            $this->command->warn("Media failed for {$product->name}. Marked as draft. Reason: {$e->getMessage()}");
        }
    }

    protected function getFromStorage(string $path): array
    {
        $fullPath = storage_path('app/'.$path);

        if (! file_exists($fullPath)) {
            throw new Exception("File not found: {$path}. Full path: {$fullPath}");
        }

        $content = file_get_contents($fullPath);
        if (! $content) {
            throw new Exception("Empty file: {$path}");
        }

        $decoded = json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in {$path}: ".json_last_error_msg());
        }

        return $decoded;
    }

    protected function assignProductCategories(Product $product, Category $baseCategory, object $productData): void
    {
        $additionalCategoryIds = $this->resolveAdditionalCategoryIds($productData);
        if (empty($additionalCategoryIds)) {
            $additionalCategoryIds = $this->randomCategoryIds($baseCategory, 3);
        }
        $categoryIds = array_unique(array_merge([$baseCategory->id], $additionalCategoryIds));

        if (empty($categoryIds)) {
            $categoryIds = [$baseCategory->id];
        }

        $product->categories()->sync($categoryIds);
    }

    protected function resolveAdditionalCategoryIds(object $productData): array
    {
        $urls = [];

        if (isset($productData->categories)) {
            $urls = array_merge($urls, $this->normalizeCategorySlugs($productData->categories));
        }

        if (isset($productData->category_slugs)) {
            $urls = array_merge($urls, $this->normalizeCategorySlugs($productData->category_slugs));
        }

        if (empty($urls)) {
            return [];
        }

        return Category::query()
            ->whereIn('url', array_unique($urls))
            ->pluck('id')
            ->toArray();
    }

    protected function normalizeCategorySlugs(mixed $value): array
    {
        if (is_string($value)) {
            return [$value];
        }

        if (is_iterable($value)) {
            return array_map('strval', (array) $value);
        }

        return [];
    }

    protected function randomCategoryIds(Category $baseCategory, int $limit = 3): array
    {
        return Category::query()
            ->where('status', true)
            ->where('id', '!=', $baseCategory->id)
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id')
            ->toArray();
    }
}
