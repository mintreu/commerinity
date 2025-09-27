<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelProductCatalogue\Services\ProductCreationService;
use Mintreu\Toolkit\Casts\PublishableStatusCast;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the database with necessary data
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Test creating a simple product.
     *
     * @return void
     */
    public function test_create_simple_product()
    {
        $masalaFilterGroup = FilterGroup::with('filters.options')->where('name', 'Spices & Masala')->first();

        $productData = [
            'name' => 'Test Simple Product',
            'url' => 'test-simple-product',
            'sku' => 'TSP001',
            'price' => 9.99,
            'type' => ProductTypeCast::SIMPLE,
            'status' => PublishableStatusCast::PUBLISHED->value,
            'filter_group_id' => $masalaFilterGroup->id,
            'filter_options' => $this->mapFilterOptions($masalaFilterGroup, ProductTypeCast::SIMPLE->value),
        ];

        $product = ProductCreationService::make($productData)->create();

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Simple Product', $product->name);
        $this->assertEquals(ProductTypeCast::SIMPLE, $product->type);
    }

    /**
     * Test creating a configurable product.
     *
     * @return void
     */
    public function test_create_configurable_product()
    {
        $masalaFilterGroup = FilterGroup::with('filters.options')->where('name', 'Spices & Masala')->first();

        $productData = [
            'name' => 'Test Configurable Product',
            'url' => 'test-configurable-product',
            'sku' => 'TCP001',
            'price' => 19.99,
            'type' => ProductTypeCast::CONFIGURABLE,
            'status' => PublishableStatusCast::PUBLISHED->value,
            'filter_group_id' => $masalaFilterGroup->id,
            'filter_options' => $this->mapFilterOptions($masalaFilterGroup, ProductTypeCast::CONFIGURABLE->value),
        ];

        $product = ProductCreationService::make($productData)->create();

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Configurable Product', $product->name);
        $this->assertEquals(ProductTypeCast::CONFIGURABLE, $product->type);
        $this->assertTrue($product->variants->isNotEmpty());
    }

    /**
     * Test reading a product.
     *
     * @return void
     */
    public function test_read_product()
    {
        $product = Product::first();
        $foundProduct = Product::find($product->id);

        $this->assertEquals($product->name, $foundProduct->name);
    }

    /**
     * Test updating a product.
     *
     * @return void
     */
    public function test_update_product()
    {
        $product = Product::first();
        $newName = 'Updated Product Name';

        $product->update(['name' => $newName]);

        $updatedProduct = Product::find($product->id);

        $this->assertEquals($newName, $updatedProduct->name);
    }

    /**
     * Test deleting a product.
     *
     * @return void
     */
    public function test_delete_product()
    {
        $product = Product::first();
        $productId = $product->id;

        $product->delete();

        $this->assertDatabaseMissing('products', ['id' => $productId]);
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
}