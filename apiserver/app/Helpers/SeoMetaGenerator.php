<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\Product;

/**
 * SEO Meta Generator
 * Generates comprehensive SEO metadata for categories and products
 */
class SeoMetaGenerator
{
    /**
     * Generate SEO meta for a category
     */
    public static function forCategory(Category $category): array
    {
        $name = $category->name;
        $description = $category->desc ?? "Shop {$name} products at Mintreu. Quality products with exclusive member rewards.";

        return [
            'title' => "{$name} - Shop Premium Products | Mintreu",
            'description' => $description,
            'keywords' => self::generateKeywords($name, 'category'),
            'og_title' => "Buy {$name} Online | Mintreu",
            'og_description' => $description,
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => "{$name} | Mintreu",
            'twitter_description' => $description,
            'robots' => 'index,follow',
        ];
    }

    /**
     * Generate SEO meta for a product
     */
    public static function forProduct(Product $product): array
    {
        $name = $product->name;
        $category = $product->category?->name ?? 'Products';
        $shortDesc = $product->short_description ?? strip_tags($product->description ?? '');
        $description = $shortDesc ?: "Buy {$name} online at Mintreu. Premium quality with best prices and exclusive member rewards.";

        // Truncate description to 160 characters for meta
        $metaDescription = strlen($description) > 160
            ? substr($description, 0, 157).'...'
            : $description;

        return [
            'title' => "{$name} - Buy Online | Mintreu",
            'description' => $metaDescription,
            'keywords' => self::generateKeywords($name, 'product', $category),
            'og_title' => "Buy {$name} Online | Best Price",
            'og_description' => $metaDescription,
            'og_type' => 'product',
            'og_price_amount' => $product->getPrice() / 100, // Convert paise to rupees
            'og_price_currency' => 'INR',
            'og_availability' => $product->total_stock > 0 ? 'in stock' : 'out of stock',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => "{$name} | Mintreu",
            'twitter_description' => $metaDescription,
            'robots' => 'index,follow',
            'product_brand' => 'Mintreu',
            'product_condition' => 'new',
        ];
    }

    /**
     * Generate relevant keywords
     */
    private static function generateKeywords(string $name, string $type, ?string $category = null): array
    {
        $keywords = [
            'mintreu',
            'online shopping',
            'buy online',
            strtolower($name),
        ];

        if ($type === 'product') {
            $keywords[] = 'best price';
            $keywords[] = 'premium quality';
            if ($category) {
                $keywords[] = strtolower($category);
            }
        }

        if ($type === 'category') {
            $keywords[] = 'shop '.strtolower($name);
            $keywords[] = strtolower($name).' online';
        }

        return array_values(array_unique($keywords));
    }
}
