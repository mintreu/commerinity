<?php

declare(strict_types=1);

namespace App\Http\Resources\Ecommerce;

use App\Casts\ProductStatusCast;
use App\Http\Resources\ImageResource;
use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ecommerce\Product
 */
final class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // FIFO: Get first available stock
        // Unified stock resolution (parent OR variant)
        $stock = $this->availableStocks->first()
            ?? $this->variants
                ->flatMap->availableStocks
                ->first();

        $inStock = $this->total_stock > 0;
        $totalStock = $this->total_stock;

// Unified price (single source of truth)
        $originalPrice = $this->getPrice();


        // Check for active sale (from setSaleInfo or loaded relationship)
        $saleInfo = $this->saleInfo ?? ($this->relationLoaded('activeSaleInfo') ? $this->activeSaleInfo : null);
        $salePrice = null;
        $discountPercent = null;
        $saleName = null;
        $saleEndsAt = null;

        if (is_array($saleInfo)) {
            $salePrice = $this->calculateSalePrice($originalPrice, $saleInfo);
            if ($salePrice && $salePrice < $originalPrice) {
                $discountPercent = round((($originalPrice - $salePrice) / $originalPrice) * 100);
                $saleName = $saleInfo['name'];
                $saleEndsAt = $saleInfo['ends_at']?->toIso8601String();
            } else {
                $salePrice = null;
            }
        }

        $displayPrice = $salePrice ?? $originalPrice;

        // Build gallery
        $gallery = $this->getProductGallery();

        // Format variants
        $variants = $this->variants->map(function ($variant) {
            $variantStock = $variant->availableStocks->first();
            $variantOriginalPrice = $variantStock?->getEffectivePrice() ?? $variant->price;

            return [
                'name' => $variant->name,
                'slug' => $variant->url,
                'sku' => $variant->sku,
                'price' => $variantOriginalPrice,
                'price_formatted' => MoneyService::format($variantOriginalPrice),
                'image' => $this->formatVariantImage($variant),
                'in_stock' => $variant->availableStocks->count() > 0,
                'bv' => $variantStock?->bv ?? 0,
                'pv' => $variantStock?->pv ?? 0,
                'reward_points' => $variantStock?->reward_points ?? 0,
                'filter_options' => $variant->filterOptions->map(fn ($opt) => [
                    'filter' => $opt->filter?->name,
                    'value' => $opt->value,
                ]),
            ];
        });

        // Format filter options grouped by filter
        $filterOptions = $this->filterOptions->groupBy('filter_id')->map(function ($options) {
            $filter = $options->first()->filter;

            return [
                'filter_name' => $filter?->name,
                'options' => $options->map(fn ($opt) => [
                    'value' => $opt->value,
                ]),
            ];
        })->values();

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->url,
            'sku' => $this->sku,
            'description' => $this->description,
            'short_description' => $this->short_description,
            // Pricing
            'price' => $displayPrice,
            'price_formatted' => MoneyService::format($displayPrice),
            'original_price' => $salePrice ? $originalPrice : null,
            'original_price_formatted' => $salePrice ? MoneyService::format($originalPrice) : null,
            'discount_percent' => $discountPercent,
            'sale_name' => $saleName,
            'sale_ends_at' => $saleEndsAt,
            // Category
            'category' => $this->category ? [
                'name' => $this->category->name,
                'slug' => $this->category->url,
            ] : null,
            'gallery' => $gallery,
            'in_stock' => $inStock,
            'stock_quantity' => $totalStock,
            'view_count' => $this->view_count,
            // Return policy
            'is_returnable' => $this->is_returnable,
            'return_days' => $this->return_days,
            // Affiliate points
            'bv' => $stock?->bv ?? 0,
            'pv' => $stock?->pv ?? 0,
            'reward_points' => $stock?->reward_points ?? 0,
            // Variants
            'has_variants' => $variants->isNotEmpty(),
            'variants' => $variants,
            'filter_options' => $filterOptions,
        ];
    }

    private function calculateSalePrice(int $originalPrice, array $saleInfo): ?int
    {
        if (isset($saleInfo['sale_product'])) {
            return $saleInfo['sale_product']->getFinalPrice($originalPrice);
        }

        if (isset($saleInfo['sale'])) {
            return $saleInfo['sale']->calculatePrice($originalPrice);
        }

        return null;
    }

    private function getProductGallery(): array
    {
        $gallery = [];

        // Add display image first
        $displayMedia = $this->getFirstMedia('displayImage');
        if ($displayMedia) {
            $gallery[] = $this->formatMediaItem($displayMedia);
        }

        // Add banner images
        foreach ($this->getMedia('bannerImage') as $media) {
            $gallery[] = $this->formatMediaItem($media);
        }

        return $gallery;
    }

    private function formatVariantImage(mixed $variant): ?array
    {
        $displayMedia = $variant->getFirstMedia('displayImage');

        if (! $displayMedia) {
            return null;
        }

        return (new ImageResource($displayMedia))->toArray(request());
    }

    private function formatMediaItem($media): array
    {
        return [
            ...(new ImageResource($media))->toArray(request()),
        ];
    }

    /**
     * Set sale info for price calculation
     */
    public function setSaleInfo(?array $saleInfo): self
    {
        $this->saleInfo = $saleInfo;
        return $this;
    }
}
