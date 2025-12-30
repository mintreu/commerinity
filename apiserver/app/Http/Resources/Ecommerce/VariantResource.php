<?php

declare(strict_types=1);

namespace App\Http\Resources\Ecommerce;

use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ecommerce\Product
 */
final class VariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $stock = $this->availableStocks->first();
        $originalPrice = $stock?->getEffectivePrice() ?? $this->price;

        // Check sale for variant
        $saleInfo = $this->getActiveSaleInfo();
        $salePrice = null;
        $discountPercent = null;

        if ($saleInfo) {
            $salePrice = $this->calculateSalePrice($originalPrice, $saleInfo);
            if ($salePrice && $salePrice < $originalPrice) {
                $discountPercent = round((($originalPrice - $salePrice) / $originalPrice) * 100);
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
            'price' => $displayPrice,
            'price_formatted' => MoneyService::format($displayPrice),
            'original_price' => $salePrice ? $originalPrice : null,
            'original_price_formatted' => $salePrice ? MoneyService::format($originalPrice) : null,
            'discount_percent' => $discountPercent,
            'image' => $this->formatImage(),
            'in_stock' => $this->availableStocks->count() > 0,
            'bv' => $stock?->bv ?? 0,
            'pv' => $stock?->pv ?? 0,
            'reward_points' => $stock?->reward_points ?? 0,
            'filter_options' => $this->formatFilterOptions(),
        ];
    }

    /**
     * Get active sale info
     */
    private function getActiveSaleInfo(): ?array
    {
        if ($this->relationLoaded('activeSaleInfo')) {
            return $this->activeSaleInfo;
        }

        return null;
    }

    /**
     * Calculate sale price
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
     * Format variant image
     */
    private function formatImage(): ?array
    {
        $displayMedia = $this->getFirstMedia('displayImage');

        if (! $displayMedia) {
            return null;
        }

        return [
            'url' => $displayMedia->getUrl(),
            'thumbnail' => $displayMedia->hasGeneratedConversion('thumb')
                ? $displayMedia->getUrl('thumb')
                : $displayMedia->getUrl(),
        ];
    }

    /**
     * Format filter options
     */
    private function formatFilterOptions(): array
    {
        if (! $this->relationLoaded('filterOptions')) {
            return [];
        }

        return $this->filterOptions->map(fn ($opt) => [
            'filter' => $opt->filter?->name,
            'value' => $opt->value,
        ])->toArray();
    }
}
