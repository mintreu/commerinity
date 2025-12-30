<?php

declare(strict_types=1);

namespace App\Http\Resources\Ecommerce;

use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ecommerce\Product
 */
final class ProductDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // FIFO: Get first available stock
        $stock = $this->availableStocks->first();
        $inStock = $this->availableStocks->count() > 0;
        $totalStock = $this->availableStocks->sum('in_stock_quantity');

        // Get price from stock
        $originalPrice = $stock?->getEffectivePrice() ?? $this->price;

        // Check for active sale
        $saleInfo = $this->getActiveSaleInfo();
        $salePrice = null;
        $discountPercent = null;
        $saleName = null;
        $saleEndsAt = null;

        if ($saleInfo) {
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

        return [
            'id' => $this->id,
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
            'category' => new CategoryBriefResource($this->whenLoaded('category')),
            // Gallery
            'gallery' => $this->formatGallery(),
            // Stock
            'in_stock' => $inStock,
            'stock_quantity' => $totalStock,
            'view_count' => $this->view_count,
            // Return policy
            'is_returnable' => $this->is_returnable,
            'return_days' => $this->return_days,
            // Affiliate points from FIFO stock
            'bv' => $stock?->bv ?? 0,
            'pv' => $stock?->pv ?? 0,
            'reward_points' => $stock?->reward_points ?? 0,
            // Variants & options
            'has_variants' => $this->variants->isNotEmpty(),
            'variants' => VariantResource::collection($this->whenLoaded('variants')),
            'filter_options' => $this->formatFilterOptions(),
        ];
    }

    /**
     * Get active sale info from loaded relationship
     */
    private function getActiveSaleInfo(): ?array
    {
        if ($this->relationLoaded('activeSaleInfo')) {
            return $this->activeSaleInfo;
        }

        return null;
    }

    /**
     * Calculate sale price from sale info
     */
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

    /**
     * Format product gallery images
     */
    private function formatGallery(): array
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

    /**
     * Format a single media item
     */
    private function formatMediaItem($media): array
    {
        $hasResponsive = $media->hasResponsiveImages();

        return [
            'id' => $media->id,
            'url' => $media->getUrl(),
            'thumbnail' => $media->hasGeneratedConversion('thumb')
                ? $media->getUrl('thumb')
                : $media->getUrl(),
            'srcset' => $hasResponsive ? $media->getSrcset() : null,
        ];
    }

    /**
     * Format filter options grouped by filter
     */
    private function formatFilterOptions(): array
    {
        if (! $this->relationLoaded('filterOptions')) {
            return [];
        }

        return $this->filterOptions
            ->groupBy('filter_id')
            ->map(function ($options) {
                $filter = $options->first()->filter;

                return [
                    'filter_name' => $filter?->name,
                    'options' => $options->map(fn ($opt) => [
                        'id' => $opt->id,
                        'value' => $opt->value,
                        'swatch' => $opt->swatch_value,
                    ]),
                ];
            })
            ->values()
            ->toArray();
    }
}
