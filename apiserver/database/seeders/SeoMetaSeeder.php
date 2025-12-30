<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Helpers\SeoMetaGenerator;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;
use Illuminate\Database\Seeder;

class SeoMetaSeeder extends Seeder
{
    /**
     * Generate and populate SEO meta for all categories and products
     */
    public function run(): void
    {
        $this->command->info('🔍 Generating SEO Meta Data...');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Update categories
        $this->command->info("\n📁 Processing Categories...");
        $categoryCount = 0;

        Category::chunk(50, function ($categories) use (&$categoryCount) {
            foreach ($categories as $category) {
                $seoMeta = SeoMetaGenerator::forCategory($category);
                $category->update(['seo_meta' => $seoMeta]);
                $categoryCount++;
                $this->command->info("  ✓ {$category->name}");
            }
        });

        // Update products
        $this->command->info("\n📦 Processing Products...");
        $productCount = 0;

        Product::with('category')->chunk(100, function ($products) use (&$productCount) {
            foreach ($products as $product) {
                $seoMeta = SeoMetaGenerator::forProduct($product);
                $product->update(['seo_meta' => $seoMeta]);
                $productCount++;
                $this->command->info("  ✓ {$product->name}");
            }
        });

        $this->command->info("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info('🎉 SEO Meta Generation Complete!');
        $this->command->info("   Categories: {$categoryCount}");
        $this->command->info("   Products: {$productCount}");
    }
}
