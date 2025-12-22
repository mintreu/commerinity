<?php
//
//use App\Casts\GstTaxCast;
//use App\Models\User;
//use Illuminate\Foundation\Testing\RefreshDatabase;
//use Mintreu\LaravelCategory\Models\Category;
//use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
//use Mintreu\LaravelProductCatalogue\Models\FilterGroup;
//use Mintreu\LaravelProductCatalogue\Models\Product;
//use Mintreu\LaravelProductCatalogue\Services\ProductManager;
//use Mintreu\Toolkit\Casts\PublishableStatusCast;
//
//
///**
// * Setup test environment by running necessary seeders
// */
//beforeEach(function () {
//    // Run seeders directly
//    (new \Database\Seeders\CategorySeeder())->run();
//    (new \Database\Seeders\FilterSeeder())->run();
//
//    // Pull actual seeded records
//    $this->masalaCategory = Category::firstWhere('url', 'spices-masalas');
//    $this->masalaFilterGroup = FilterGroup::with('filters.options')
//        ->where('name', 'Spices & Masala')
//        ->first();
//
//    // Helper to map filter options (from ProductDemoSeeder)
//    $this->mapFilterOptions = function ($filterGroup, string $productType) {
//        $isConfigurable = $productType === 'configurable';
//
//        return $filterGroup->filters->mapWithKeys(function ($filter) use ($isConfigurable) {
//            $options = $filter->options;
//
//            if ($options->isEmpty()) {
//                return [(string) $filter->id => []];
//            }
//
//            if ($isConfigurable) {
//                $selected = $options->random(min(2, $options->count()))->pluck('id')->values()->toArray();
//            } else {
//                $selected = [$options->random()->id];
//            }
//
//            // Always cast to string to match form data format
//            $selected = array_map('strval', $selected);
//
//            return [(string) $filter->id => $selected];
//        })->toArray();
//    };
//});
//
//
//
//describe('ProductManager - Configurable Product Creation (Like ProductDemoSeeder)', function () {
//    it('can create configurable product with factory raw data like seeder', function () {
//
//        // Exactly like ProductDemoSeeder does it
//        $productData = Product::factory()->raw([
//            'name' => 'Turmeric Powder',
//            'url' => 'turmeric-powder',
//            'sku' => 'TUR-PWD',
//            'price' => fake()->randomElement([12050, 15000, 8000, 45000]),
//            'type' => ProductTypeCast::CONFIGURABLE,
//            'status' => PublishableStatusCast::PUBLISHED->value,
//            'filter_group_id' => $this->masalaFilterGroup->id,
//            'filter_options' => ($this->mapFilterOptions)($this->masalaFilterGroup, 'configurable'),
//            'tax_slab' => GstTaxCast::GST_5->value,
//        ]);
//
//        $product = ProductManager::create($productData);
//
//        expect($product)->toBeInstanceOf(Product::class)
//            ->and($product->type)->toBe(ProductTypeCast::CONFIGURABLE)
//            ->and($product->status)->toBe(PublishableStatusCast::PUBLISHED)
//            ->and($product->variants->count())->toBeGreaterThan(0);
//
//        $this->assertDatabaseHas('products', [
//            'sku' => 'TUR-PWD',
//            'type' => 'configurable',
//            'parent_id' => null,
//        ]);
//    });
//
//    it('can update variant status and max_quantity after creation like seeder', function () {
//        $productData = Product::factory()->raw([
//            'name' => 'Coriander Powder',
//            'url' => 'coriander-powder',
//            'sku' => 'COR-PWD',
//            'price' => 15000,
//            'type' => ProductTypeCast::CONFIGURABLE,
//            'status' => PublishableStatusCast::PUBLISHED->value,
//            'filter_group_id' => $this->masalaFilterGroup->id,
//            'filter_options' => ($this->mapFilterOptions)($this->masalaFilterGroup, 'configurable'),
//            'tax_slab' => GstTaxCast::GST_5->value,
//        ]);
//
//        $product = ProductManager::create($productData);
//
//        // Load variants and update them (exactly like ProductDemoSeeder)
//        $product->load('variants');
//        $product->variants()->each(function ($variant) {
//            $variant->update([
//                'status' => PublishableStatusCast::PUBLISHED,
//                'max_quantity' => fake()->numberBetween(3, 12),
//            ]);
//        });
//
//        $product->refresh();
//
//        expect($product->variants->first()->status)->toBe(PublishableStatusCast::PUBLISHED)
//            ->and($product->variants->first()->max_quantity)->toBeGreaterThanOrEqual(3)
//            ->and($product->variants->first()->max_quantity)->toBeLessThanOrEqual(12);
//    });
//
//    it('can attach categories with base_category like seeder', function () {
//        $productData = Product::factory()->raw([
//            'name' => 'Red Chilli Powder',
//            'url' => 'red-chilli-powder',
//            'sku' => 'CHILLI-PWD',
//            'price' => 12050,
//            'type' => ProductTypeCast::CONFIGURABLE,
//            'status' => PublishableStatusCast::PUBLISHED->value,
//            'filter_group_id' => $this->masalaFilterGroup->id,
//            'filter_options' => ($this->mapFilterOptions)($this->masalaFilterGroup, 'configurable'),
//            'tax_slab' => GstTaxCast::GST_5->value,
//        ]);
//
//        $product = ProductManager::create($productData);
//
//        // Attach category exactly like ProductDemoSeeder
//        $product->categories()->attach([
//            $this->masalaCategory->id => [
//                'base_category' => $this->masalaCategory->parent_id ?? $this->masalaCategory->id,
//            ],
//        ]);
//
//        expect($product->categories)->toHaveCount(1);
//
//        $pivot = $product->categories->first()->pivot;
//        expect($pivot->base_category)->toBe($this->masalaCategory->parent_id ?? $this->masalaCategory->id);
//    });
//
//    it('generates variants with inherited parent data', function () {
//        $productData = Product::factory()->raw([
//            'name' => 'Garam Masala',
//            'url' => 'garam-masala',
//            'sku' => 'GARAM-MASALA',
//            'price' => 45000,
//            'description' => 'Premium garam masala blend',
//            'short_description' => 'Garam Masala',
//            'type' => ProductTypeCast::CONFIGURABLE,
//            'status' => PublishableStatusCast::PUBLISHED->value,
//            'filter_group_id' => $this->masalaFilterGroup->id,
//            'filter_options' => ($this->mapFilterOptions)($this->masalaFilterGroup, 'configurable'),
//            'tax_slab' => '12',
//            'min_quantity' => 1,
//            'max_quantity' => 10,
//            'reward_point' => 50,
//        ]);
//
//        $product = ProductManager::create($productData);
//
//        $variant = $product->variants->first();
//
//        // Verify variant inherits parent data
//        expect($variant->name)->toBe('Garam Masala')
//            ->and($variant->description)->toBe('Premium garam masala blend')
//            ->and($variant->short_description)->toBe('Garam Masala')
//            ->and($variant->price)->toBe(45000)
//            ->and($variant->tax_slab)->toBe(GstTaxCast::GST_12->value)
//            ->and($variant->min_quantity)->toBe(1)
//            ->and($variant->reward_point)->toBe(50);
//    });
//
//    it('uses random filter options selection for configurable products', function () {
//        // mapFilterOptions should select random 2 options per filter for configurable
//        $filterOptions = ($this->mapFilterOptions)($this->masalaFilterGroup, 'configurable');
//
//        $productData = Product::factory()->raw([
//            'name' => 'Random Filter Test',
//            'url' => 'random-filter-test',
//            'sku' => 'RANDOM-TEST',
//            'price' => 10000,
//            'type' => ProductTypeCast::CONFIGURABLE,
//            'status' => PublishableStatusCast::PUBLISHED->value,
//            'filter_group_id' => $this->masalaFilterGroup->id,
//            'filter_options' => $filterOptions,
//            'tax_slab' => GstTaxCast::GST_18->value,
//        ]);
//
//        $product = ProductManager::create($productData);
//
//        // Should have created variants based on random filter options
//        expect($product->variants->count())->toBeGreaterThan(0);
//
//        // All filter option IDs should be strings (like seeder does)
//        foreach ($filterOptions as $filterId => $optionIds) {
//            // expect($filterId)->toBeString();
//            foreach ($optionIds as $optionId) {
//                expect($optionId)->toBeString();
//            }
//        }
//    });
//});
//
//describe('ProductManager - Simple Product Creation (For Variants)', function () {
//    it('can create simple product with single filter option like seeder', function () {
//        // For simple products, mapFilterOptions selects only 1 random option per filter
//        $productData = Product::factory()->raw([
//            'name' => 'Simple Masala Product',
//            'url' => 'simple-masala-product',
//            'sku' => 'SIMPLE-MASALA',
//            'price' => 8000,
//            'type' => ProductTypeCast::SIMPLE,
//            'status' => PublishableStatusCast::PUBLISHED->value,
//            'filter_group_id' => $this->masalaFilterGroup->id,
//            'filter_options' => ($this->mapFilterOptions)($this->masalaFilterGroup, 'simple'),
//            'tax_slab' => GstTaxCast::GST_5->value,
//        ]);
//
//        $product = ProductManager::create($productData);
//
//        expect($product)->toBeInstanceOf(Product::class)
//            ->and($product->type)->toBe(ProductTypeCast::SIMPLE);
//
//        // Simple products should have filter options attached
//        expect($product->filterOptions->count())->toBeGreaterThan(0);
//    });
//});
//
////describe('ProductMgmtService - Product Update', function () {
////    it('can update configurable product and its variants', function () {
////        $productData = Product::factory()->raw([
////            'name' => 'Update Test Product',
////            'url' => 'update-test-product',
////            'sku' => 'UPDATE-TEST',
////            'price' => 10000,
////            'type' => ProductTypeCast::CONFIGURABLE,
////            'status' => PublishableStatusCast::DRAFT->value,
////            'filter_group_id' => $this->masalaFilterGroup->id,
////            'filter_options' => ($this->mapFilterOptions)($this->masalaFilterGroup, 'configurable'),
////            'tax_slab' => GstTaxCast::GST_12->value,
////        ]);
////
////        $product = ProductManager::create($productData);
////
////        // Update product
////        $updatedProduct = ProductCreationService::make()
////            ->setProduct($product)
////            ->update([
////                'name' => 'Updated Product Name',
////                'price' => 20000,
////                'status' => PublishableStatusCast::PUBLISHED->value,
////                'type' => ProductTypeCast::CONFIGURABLE,
////                'filter_options' => ($this->mapFilterOptions)($this->masalaFilterGroup, 'configurable'),
////            ]);
////
////        expect($updatedProduct->name)->toBe('Updated Product Name')
////            ->and($updatedProduct->price)->toBe(20000)
////            ->and($updatedProduct->status)->toBe(PublishableStatusCast::PUBLISHED);
////    });
////
////    it('smart updates variants when filter options change', function () {
////        // Get specific filter options for controlled test
////        $filters = $this->masalaFilterGroup->filters;
////        $filter1 = $filters->first();
////        $filter2 = $filters->skip(1)->first() ?? $filter1;
////
////        $initialFilterOptions = [
////            (string) $filter1->id => [$filter1->options->first()->id],
////            (string) $filter2->id => [$filter2->options->first()->id],
////        ];
////
////        $productData = Product::factory()->raw([
////            'name' => 'Smart Update Test',
////            'url' => 'smart-update-test',
////            'sku' => 'SMART-UPDATE',
////            'price' => 10000,
////            'type' => ProductTypeCast::CONFIGURABLE,
////            'status' => PublishableStatusCast::PUBLISHED->value,
////            'filter_group_id' => $this->masalaFilterGroup->id,
////            'filter_options' => $initialFilterOptions,
////            'tax_slab' => GstTaxCast::GST_18->value,
////        ]);
////
////        $product = ProductManager::create($productData);
////        $initialVariantCount = $product->variants->count();
////
////        // Add more filter options
////        $updatedFilterOptions = [
////            (string) $filter1->id => $filter1->options->take(2)->pluck('id')->map(fn($id) => (string) $id)->toArray(),
////            (string) $filter2->id => [$filter2->options->first()->id],
////        ];
////
////        ProductCreationService::make()
////            ->setProduct($product)
////            ->update([
////                'type' => ProductTypeCast::CONFIGURABLE,
////                'filter_options' => $updatedFilterOptions,
////                'name' => 'Smart Update Test',
////                'sku' => 'SMART-UPDATE',
////            ]);
////
////        $product->refresh();
////
////        // Should have more variants now
////        expect($product->variants->count())->toBeGreaterThan($initialVariantCount);
////    });
////});
////
////describe('ProductMgmtService - Product Deletion', function () {
////    it('deletes configurable product and all variants', function () {
////        $productData = Product::factory()->raw([
////            'name' => 'Delete Test Product',
////            'url' => 'delete-test-product',
////            'sku' => 'DELETE-TEST',
////            'price' => 10000,
////            'type' => ProductTypeCast::CONFIGURABLE,
////            'status' => PublishableStatusCast::PUBLISHED->value,
////            'filter_group_id' => $this->masalaFilterGroup->id,
////            'filter_options' => ($this->mapFilterOptions)($this->masalaFilterGroup, 'configurable'),
////            'tax_slab' => GstTaxCast::GST_5->value,
////        ]);
////
////        $product = ProductManager::create($productData);
////        $variantIds = $product->variants->pluck('id')->toArray();
////
////        // Delete product
////        ProductCreationService::make()
////            ->setProduct($product)
////            ->delete();
////
////        // Parent and all variants should be deleted
////        $this->assertDatabaseMissing('products', ['id' => $product->id]);
////
////        foreach ($variantIds as $variantId) {
////            $this->assertDatabaseMissing('products', ['id' => $variantId]);
////        }
////    });
////});
//
//describe('ProductManager - Complete ProductDemoSeeder Workflow', function () {
//    it('mimics complete ProductDemoSeeder product creation workflow', function () {
//        // Step 1: Create product with factory raw (like seeder)
//        $productData = Product::factory()->raw([
//            'name' => 'Complete Workflow Test',
//            'url' => 'complete-workflow-test',
//            'sku' => 'COMPLETE-TEST',
//            'price' => fake()->randomElement([12050, 15000, 8000, 45000]),
//            'type' => ProductTypeCast::CONFIGURABLE,
//            'status' => PublishableStatusCast::PUBLISHED->value,
//            'filter_group_id' => $this->masalaFilterGroup->id,
//            'filter_options' => ($this->mapFilterOptions)($this->masalaFilterGroup, 'configurable'),
//            'tax_slab' => GstTaxCast::GST_28->value,
//        ]);
//
//        $product = ProductManager::create($productData);
//
//        // Step 2: Update variants (like seeder does)
//        $product->load('variants');
//        $product->variants()->each(function ($variant) {
//            $variant->update([
//                'status' => PublishableStatusCast::PUBLISHED,
//                'max_quantity' => fake()->numberBetween(3, 12),
//            ]);
//        });
//
//        // Step 3: Attach category (like seeder)
//        $product->categories()->attach([
//            $this->masalaCategory->id => [
//                'base_category' => $this->masalaCategory->parent_id ?? $this->masalaCategory->id,
//            ],
//        ]);
//
//        // Step 4: Verify everything
//        $product->refresh();
//
//        expect($product)->toBeInstanceOf(Product::class)
//            ->and($product->type)->toBe(ProductTypeCast::CONFIGURABLE)
//            ->and($product->status)->toBe(PublishableStatusCast::PUBLISHED)
//            ->and($product->variants->count())->toBeGreaterThan(0)
//            ->and($product->categories->count())->toBe(1)
//            ->and($product->variants->first()->status)->toBe(PublishableStatusCast::PUBLISHED)
//            ->and($product->variants->first()->max_quantity)->toBeGreaterThanOrEqual(3);
//    });
//});
