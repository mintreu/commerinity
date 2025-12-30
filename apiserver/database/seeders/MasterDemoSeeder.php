<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\FilterGroup;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductEngagement;
use App\Models\Ecommerce\ProductStock;
use App\Models\Ecommerce\ProductWishlist;
use App\Models\Ecommerce\Sale;
use App\Models\Ecommerce\SaleProduct;
use App\Models\User;
use App\Services\Ecommerce\ProductManager;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * MasterDemoSeeder - Seeds complete demo data using ProductManager
 *
 * Seeds:
 * - Products with variants (using ProductManager)
 * - Media attachments
 * - Stock entries
 * - Sales/promotions
 * - Filter options on products
 */
class MasterDemoSeeder extends Seeder
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

    private array $reviewTexts = [
        'Great product! Exactly what I needed.',
        'Excellent quality, fast delivery. Will buy again.',
        'Good value for money. Satisfied with purchase.',
        'Amazing product! Highly recommended.',
        'Quality is top-notch. Very happy with this.',
        'Decent product, met my expectations.',
        'Perfect! Just as described.',
        'Very good quality. Worth every rupee.',
        'Love it! Great purchase.',
        'Excellent product, fast shipping.',
    ];

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('============================================');
        $this->command->info(' MASTER DEMO SEEDER - Using ProductManager');
        $this->command->info('============================================');
        $this->command->info('');

        // Seed products for each category
        $totalProducts = 0;
        foreach ($this->categoriesToSeed as $categoryUrl) {
            $this->command->info("\nProcessing category: {$categoryUrl}");

            try {
                $count = $this->seedCategoryProducts($categoryUrl);
                $totalProducts += $count;
                $this->command->info("  Seeded {$count} products");
            } catch (Exception $e) {
                $this->command->error("  Failed: {$e->getMessage()}");
            }
        }

        // Create sales/promotions
        $this->createSales();
        $this->command->info("\nCreated sales/promotions");

        // Summary
        $this->command->info('');
        $this->command->info('============================================');
        $this->command->info(' SEEDING COMPLETE');
        $this->command->info('============================================');
        $this->command->info(" Total Products: {$totalProducts}");
        $this->command->info(' Reviews: '.ProductEngagement::count());
        $this->command->info(' Wishlists: '.ProductWishlist::count());
        $this->command->info(' Stocks: '.ProductStock::count());
        $this->command->info(' Sales: '.Sale::count());
        $this->command->info('============================================');
        $this->command->info('');
    }

    protected function seedCategoryProducts(string $categoryUrl): int
    {
        $category = Category::where('url', $categoryUrl)->first();
        if (! $category) {
            throw new Exception("Category '{$categoryUrl}' not found");
        }

        $filterGroupName = $this->categoryFilterMap[$categoryUrl] ?? null;
        if (! $filterGroupName) {
            throw new Exception("No filter group mapping for '{$categoryUrl}'");
        }

        $filterGroup = FilterGroup::with('filters.options')
            ->where('name', $filterGroupName)
            ->first();

        if (! $filterGroup) {
            throw new Exception("Filter group '{$filterGroupName}' not found");
        }

        $jsonPath = "private/data/products/{$categoryUrl}/{$categoryUrl}.json";
        $products = $this->getFromStorage($jsonPath);

        if (empty($products)) {
            return 0;
        }

        $seeded = 0;
        foreach ($products as $productData) {
            try {
                $product = $this->createProductWithManager($productData, $category, $filterGroup);

                // Add media
                $this->attachMediaFiles($product, $category);

                // Add stock
                $this->addStock($product);

                // Add review (random chance)
                if (random_int(1, 100) <= 70) { // 70% chance
                    $this->addReview($product);
                }

                // Add to wishlist (random chance)
                if (random_int(1, 100) <= 40) { // 40% chance
                    $this->addToWishlist($product);
                }

                $seeded++;
                $this->command->info("    {$product->name}");
            } catch (Exception $e) {
                $this->command->error("    Failed: {$productData->name} - {$e->getMessage()}");
            }
        }

        return $seeded;
    }

    /**
     * Create product using ProductManager (like old_project)
     */
    protected function createProductWithManager(object $data, Category $category, FilterGroup $filterGroup): Product
    {
        // Build filter_options using mapFilterOptions (like old_project)
        $filterOptions = $this->mapFilterOptions($filterGroup, ProductTypeCast::CONFIGURABLE->value);

        // Prepare data for ProductManager
        $productData = [
            'name' => $data->name,
            'sku' => $data->sku,
            'url' => $data->url,
            'status' => ProductStatusCast::PUBLISHED->value,
            'type' => ProductTypeCast::CONFIGURABLE->value,
            'filter_group_id' => $filterGroup->id,
            'category_id' => $category->id,
            'price' => $data->price ?? random_int(5000, 150000),
            'short_description' => $data->short_description ?? null,
            'description' => $data->description ?? null,
            'is_returnable' => true,
            'return_days' => 7,
            'filter_options' => $filterOptions,
        ];

        // Use ProductManager to create product with variants
        $product = ProductManager::create($productData);

        // Attach category after creation (ProductManager doesn't handle this)
        $product->category_id = $category->id;
        $product->save();

        return $product;
    }

    /**
     * Map filter options for product (from old_project MasterDemoProductSeeder)
     */
    protected function mapFilterOptions(FilterGroup $filterGroup, string $productType): array
    {
        $isConfigurable = $productType === ProductTypeCast::CONFIGURABLE->value;

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

            // Cast to string to match form data format
            $selected = array_map('strval', $selected);

            return [(string) $filter->id => $selected];
        })->toArray();
    }

    protected function attachMediaFiles(Product $product, Category $category): void
    {
        $dir = $category->url.'/'.$product->url.'/';
        $displayImagePath = Storage::path('private/data/products/'.$dir.$product->url.'.png');

        // Skip if media already attached
        if ($product->getFirstMediaUrl('displayImage')) {
            return;
        }

        if (file_exists($displayImagePath)) {
            $product->clearMediaCollection('displayImage');
            $product->addMedia($displayImagePath)
                ->preservingOriginal()
                ->toMediaCollection('displayImage');
        }

        // Add all images as banner images
        $allImages = Storage::disk('local')->allFiles('private/data/products/'.$dir);
        foreach ($allImages as $image) {
            $imagePath = Storage::path($image);
            if (file_exists($imagePath) && ! str_contains($imagePath, $product->url.'.png')) {
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

    protected function addStock(Product $product): void
    {
        // Skip if stock already exists
        if ($product->stocks()->exists()) {
            return;
        }

        $productPrice = $product->price;
        $landingCost = (int) ($productPrice * 0.6);
        $profit = $productPrice - $landingCost;

        ProductStock::create([
            'product_id' => $product->id,
            'init_quantity' => random_int(50, 500),
            'sold_quantity' => 0,
            'priority' => 1,
            'landing_cost' => $landingCost,
            'profit_margin' => 40.00,
            'min_quantity' => 1,
            'max_quantity' => 50,
            'bv' => (int) ($profit * 0.10),
            'pv' => (int) ($profit * 0.05),
            'reward_points' => (int) floor($profit / 100),
            'commission_rate' => 5.00,
            'is_commissionable' => true,
            'low_stock_threshold' => 10,
            'notify_on_low_stock' => true,
            'batch_number' => 'BATCH-'.strtoupper(substr(md5((string) $product->id), 0, 8)),
            'purchase_date' => now()->subDays(random_int(1, 30)),
            'expiry_date' => now()->addMonths(random_int(6, 24)),
        ]);
    }

    protected function addReview(Product $product): void
    {
        $user = User::firstWhere('email', 'test@example.com');
        if (! $user) {
            return;
        }

        // Check if review already exists
        if (ProductEngagement::where('product_id', $product->id)
            ->where('authorable_id', $user->id)
            ->where('authorable_type', User::class)
            ->exists()) {
            return;
        }

        ProductEngagement::create([
            'product_id' => $product->id,
            'authorable_id' => $user->id,
            'authorable_type' => User::class,
            'rating' => random_int(3, 5),
            'review' => $this->reviewTexts[array_rand($this->reviewTexts)],
            'helpful_votes' => random_int(0, 50),
        ]);
    }

    protected function addToWishlist(Product $product): void
    {
        $user = User::firstWhere('email', 'test@example.com');
        if (! $user) {
            return;
        }

        ProductWishlist::firstOrCreate([
            'product_id' => $product->id,
            'authorable_id' => $user->id,
            'authorable_type' => User::class,
        ]);
    }

    protected function createSales(): void
    {
        // Skip if sales already exist
        if (Sale::count() > 0) {
            return;
        }

        // Create site-wide sale
        $siteSale = Sale::create([
            'name' => 'New Year Sale',
            'condition_type' => 'match_any',
            'action_type' => 'by_percent',
            'discount_amount' => 10,
            'starts_from' => now()->subDays(5),
            'ends_till' => now()->addDays(30),
            'status' => true,
            'sort_order' => 1,
        ]);

        // Create product-specific sales
        $products = Product::whereNull('parent_id')->inRandomOrder()->limit(10)->get();

        foreach ($products as $index => $product) {
            $isPercent = random_int(0, 1) === 1;
            $discountAmount = random_int(5, 20);

            if ($isPercent) {
                $salePrice = (int) round($product->price * (1 - ($discountAmount / 100)));
            } else {
                $salePrice = max(0, $product->price - ($discountAmount * 100));
            }

            SaleProduct::create([
                'sale_id' => $siteSale->id,
                'product_id' => $product->id,
                'action_type' => $isPercent ? 'by_percent' : 'by_fixed',
                'discount_amount' => $discountAmount,
                'sale_price' => $salePrice,
                'starts_from' => now()->subDays(3),
                'ends_till' => now()->addDays(15),
                'sort_order' => $index + 1,
            ]);
        }
    }

    protected function getFromStorage(string $path): array
    {
        $fullPath = storage_path('app/'.$path);

        if (! file_exists($fullPath)) {
            return [];
        }

        $content = file_get_contents($fullPath);
        if (! $content) {
            return [];
        }

        $decoded = json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }
}
