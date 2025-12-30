<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Filter;
use App\Models\Ecommerce\FilterGroup;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductEngagement;
use App\Models\Ecommerce\ProductStock;
use App\Models\Ecommerce\ProductWishlist;
use App\Models\Ecommerce\Sale;
use App\Models\Ecommerce\SaleProduct;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * MasterDemoSeeder - Seeds complete demo data for testing
 *
 * Seeds:
 * - Products with images
 * - Product variants (configurable products)
 * - Stock entries with MLM points
 * - Reviews and ratings
 * - Wishlists
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
        $this->command->info(' MASTER DEMO SEEDER - Complete Data Seeding');
        $this->command->info('============================================');
        $this->command->info('');

        // 1. Create test user if not exists
        $testUser = $this->getOrCreateTestUser();
        $this->command->info("User: {$testUser->email}");

        // 2. Seed products for each category
        $totalProducts = 0;
        foreach ($this->categoriesToSeed as $categoryUrl) {
            $this->command->info("\nProcessing category: {$categoryUrl}");

            try {
                $count = $this->seedCategoryProducts($categoryUrl, $testUser);
                $totalProducts += $count;
                $this->command->info("  Seeded {$count} products");
            } catch (Exception $e) {
                $this->command->error("  Failed: {$e->getMessage()}");
            }
        }

        // 3. Create sales/promotions
        $this->createSales();
        $this->command->info("\nCreated sales/promotions");

        // 4. Summary
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

    protected function getOrCreateTestUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
    }

    protected function seedCategoryProducts(string $categoryUrl, User $testUser): int
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
                $product = $this->createProduct($productData, $category, $filterGroup);

                // Add media
                $this->attachMediaFiles($product, $category);

                // Add stock
                $this->addStock($product);

                // Add filter options
                $this->attachFilterOptions($product, $filterGroup);

                // Create variants for configurable products
                if ($this->isConfigurable($productData)) {
                    $this->createVariants($product, $filterGroup, $category);
                }

                // Add review (random chance)
                if (random_int(1, 100) <= 70) { // 70% chance
                    $this->addReview($product, $testUser);
                }

                // Add to wishlist (random chance)
                if (random_int(1, 100) <= 40) { // 40% chance
                    $this->addToWishlist($product, $testUser);
                }

                $seeded++;
                $this->command->info("    {$product->name}");
            } catch (Exception $e) {
                $this->command->error("    Failed: {$productData->name} - {$e->getMessage()}");
            }
        }

        return $seeded;
    }

    protected function createProduct(object $data, Category $category, FilterGroup $filterGroup): Product
    {
        $isConfigurable = $this->isConfigurable($data);

        return Product::updateOrCreate(
            ['sku' => $data->sku],
            [
                'name' => $data->name,
                'url' => $data->url,
                'status' => ProductStatusCast::PUBLISHED->value,
                'type' => $isConfigurable
                    ? ProductTypeCast::CONFIGURABLE->value
                    : ProductTypeCast::SIMPLE->value,
                'price' => $data->price ?? random_int(5000, 150000), // in paise
                'short_description' => $data->short_description ?? null,
                'description' => $data->description ?? null,
                'category_id' => $category->id,
                'filter_group_id' => $filterGroup->id,
                'is_returnable' => true,
                'return_days' => 7,
                'view_count' => random_int(10, 500),
            ]
        );
    }

    protected function isConfigurable(object $data): bool
    {
        return isset($data->configurable) && $data->configurable;
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

    protected function attachFilterOptions(Product $product, FilterGroup $filterGroup): void
    {
        // Skip if already has filter options
        if ($product->filterOptions()->exists()) {
            return;
        }

        foreach ($filterGroup->filters as $filter) {
            if ($filter->options->isEmpty()) {
                continue;
            }

            // Pick 1-2 random options for simple products
            $options = $filter->options->random(min(2, $filter->options->count()));

            // Attach with filter_id pivot data
            foreach ($options as $option) {
                $product->filterOptions()->attach($option->id, [
                    'filter_id' => $filter->id,
                ]);
            }
        }
    }

    protected function createVariants(Product $parent, FilterGroup $filterGroup, Category $category): void
    {
        // Skip if variants already exist
        if ($parent->variants()->exists()) {
            return;
        }

        // Get filter with most options (usually color or size)
        $variantFilter = $filterGroup->filters
            ->sortByDesc(fn ($f) => $f->options->count())
            ->first();

        if (! $variantFilter || $variantFilter->options->count() < 2) {
            return;
        }

        // Create up to 3 variants
        $variantOptions = $variantFilter->options->take(3);
        $index = 1;

        foreach ($variantOptions as $option) {
            $variantName = "{$parent->name} - {$option->value}";
            $variantSku = "{$parent->sku}-V{$index}";
            $variantUrl = Str::slug("{$parent->url}-{$option->value}");

            // Price variation (95-110% of parent price)
            $variantPrice = (int) ($parent->price * (random_int(95, 110) / 100));

            $variant = Product::create([
                'name' => $variantName,
                'sku' => $variantSku,
                'url' => $variantUrl,
                'status' => ProductStatusCast::PUBLISHED->value,
                'type' => ProductTypeCast::SIMPLE->value,
                'price' => $variantPrice,
                'parent_id' => $parent->id,
                'category_id' => $category->id,
                'filter_group_id' => $parent->filter_group_id,
                'short_description' => $parent->short_description,
                'is_returnable' => true,
                'return_days' => 7,
                'view_count' => 0,
            ]);

            // Attach the specific filter option to this variant
            $variant->filterOptions()->attach($option->id, [
                'filter_id' => $variantFilter->id,
            ]);

            // Add stock for variant
            $this->addStock($variant);

            // Copy parent's display image
            $parentMedia = $parent->getFirstMedia('displayImage');
            if ($parentMedia) {
                $variant->addMediaFromUrl($parentMedia->getUrl())
                    ->preservingOriginal()
                    ->toMediaCollection('displayImage');
            }

            $index++;
        }
    }

    protected function addReview(Product $product, User $user): void
    {
        // Check if review already exists for this product
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
            'rating' => random_int(3, 5), // 3-5 stars
            'review' => $this->reviewTexts[array_rand($this->reviewTexts)],
            'helpful_votes' => random_int(0, 50),
        ]);
    }

    protected function addToWishlist(Product $product, User $user): void
    {
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

        // Create site-wide sale using correct enum values
        $siteSale = Sale::create([
            'name' => 'New Year Sale',
            'condition_type' => 'match_any', // Correct enum value
            'action_type' => 'by_percent',    // Correct enum value
            'discount_amount' => 10, // 10% off
            'starts_from' => now()->subDays(5),
            'ends_till' => now()->addDays(30),
            'status' => true,
            'sort_order' => 1,
        ]);

        // Create product-specific sales for random products
        $products = Product::whereNull('parent_id')->inRandomOrder()->limit(10)->get();

        foreach ($products as $index => $product) {
            $isPercent = random_int(0, 1) === 1;
            $discountAmount = random_int(5, 20);
            $salePrice = null;

            // Calculate sale_price if action_type is by_fixed
            if (! $isPercent) {
                $salePrice = max(0, $product->price - ($discountAmount * 100)); // Convert Rs to paise
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
