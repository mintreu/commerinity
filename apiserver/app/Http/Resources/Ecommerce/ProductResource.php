<?php

declare(strict_types=1);

namespace App\Http\Resources\Ecommerce;

use App\Http\Resources\ImageResource;
use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ecommerce\Product
 */
final class ProductResource extends JsonResource
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
        $inStock = $this->total_stock > 0;

        // Get price using Product model's getPrice() method
        $originalPrice = $this->getPrice();

        // Check for active sale
        $saleInfo = $this->getActiveSaleInfo();
        $salePrice = null;
        $discountPercent = null;
        $saleName = null;
        $saleEndsAt = null;

        if (is_array($saleInfo) && $originalPrice > 0) {
            $salePrice = $this->calculateSalePrice($originalPrice, $saleInfo);
            if ($salePrice && $salePrice < $originalPrice) {
                $discountPercent = round((($originalPrice - $salePrice) / $originalPrice) * 100);
                $saleName = $saleInfo['name'] ?? null;
                $saleEndsAt = $saleInfo['ends_at']?->toIso8601String();
            } else {
                $salePrice = null;
            }
        }

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->url,
            'sku' => $this->sku,
            // Pricing
            'price' => $salePrice ?? $originalPrice,
            'price_formatted' => MoneyService::format($salePrice ?? $originalPrice),
            'original_price' => $salePrice ? $originalPrice : null,
            'original_price_formatted' => $salePrice ? MoneyService::format($originalPrice) : null,
            'discount_percent' => $discountPercent,
            'sale_name' => $saleName,
            'sale_ends_at' => $saleEndsAt,
            // Category
            'category' => new CategoryBriefResource($this->whenLoaded('category')),
            // Images
            'image' => $this->formatImage(),
            // Stock
            'in_stock' => $inStock,
            'stock_quantity' => $this->total_stock,
            // Stats
            'view_count' => $this->view_count,
            // Affiliate points from FIFO stock
            'bv' => $stock?->bv ?? 0,
            'pv' => $stock?->pv ?? 0,
            'reward_points' => $stock?->reward_points ?? 0,
        ];
    }

    /**
     * Get active sale info from loaded relationship or calculated value
     */
    private function getActiveSaleInfo(): ?array
    {
        // Check if sale info was eager loaded
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
     * Format product image
     */
    private function formatImage(): ?array
    {
        $displayMedia = $this->getFirstMedia('displayImage');

        if (! $displayMedia) {
            return null;
        }

        return (new ImageResource($displayMedia))->toArray(request());
    }
}
