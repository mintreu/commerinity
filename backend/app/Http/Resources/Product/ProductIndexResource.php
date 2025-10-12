<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mintreu\LaravelMoney\LaravelMoney;

class ProductIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activeSale = $this->getActiveSale();
        $effectivePrice = $this->getEffectivePrice();

        return [
            'name' => $this->name,
            'url' => $this->url,
            'sku' => $this->sku,
            'short_description' => $this->whenNotNull($this->short_description),
            'price' => LaravelMoney::format($this->price),
            'min_quantity' => $this->whenNotNull($this->min_quantity),
            'max_quantity' => $this->whenNotNull($this->max_quantity),
            'reward_point' => $this->whenNotNull($this->reward_point),
            'returnable' => $this->whenNotNull($this->is_returnable),
            'views' => $this->view_count,
            'thumbnail' => $this->getFirstMediaUrl('displayImage'),
            //'banner' => $this->getFirstMediaUrl('bannerImage'),

            'formatted' => [
                'regular'   => $this->price ? LaravelMoney::format($this->price) : null,
                'sale'      => $activeSale?->sale_price ? LaravelMoney::format($activeSale->sale_price) : null,
                'effective' => $effectivePrice ? LaravelMoney::format($effectivePrice) : null,
            ],
        ];
    }

    /**
     * Get the active sale (SaleProduct) for this product
     */
    protected function getActiveSale()
    {
        if (!$this->relationLoaded('sales')) {
            return null;
        }

        // sales() relationship returns SaleProduct models directly
        return $this->sales->first();
    }

    /**
     * Get the effective price (sale price if available, otherwise regular price)
     */
    protected function getEffectivePrice()
    {
        $activeSale = $this->getActiveSale();
        return $activeSale?->sale_price ?? $this->price;
    }

    /**
     * Calculate discount percentage
     */
    protected function getDiscountPercentage(): ?float
    {
        $activeSale = $this->getActiveSale();

        if (!$activeSale || !$this->price || !$activeSale->sale_price) {
            return null;
        }

//        $regularPrice = (float) $this->price->getAmount();
//        $salePrice = (float) $activeSale->sale_price->getAmount();
//

        $regularPrice = (float) $this->price->getAmount();
        $salePrice = (float) $activeSale->sale_price->getAmount();

        if ($regularPrice <= 0) {
            return null;
        }

        return round((($regularPrice - $salePrice) / $regularPrice) * 100, 2);
    }
}
