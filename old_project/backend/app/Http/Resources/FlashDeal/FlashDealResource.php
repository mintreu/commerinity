<?php

namespace App\Http\Resources\FlashDeal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mintreu\LaravelMoney\LaravelMoney;

class FlashDealResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $product = $this->product;

        // Get original price from product (LaravelMoneyCast)
        $originalPrice = $product->price->amount ?? 0;

        // Get sale data (these are integers from migration)
        $salePrice = $this->sale_price ?? 0;
        $discountAmount = $this->discount_amount ?? 0;

        // Calculate discount percentage
        $discountPercentage = $originalPrice > 0
            ? round(($discountAmount / $originalPrice) * 100)
            : 0;

        // Mock stock data (implement real stock management if needed)
        $stockTotal = rand(50, 200);
        $stockLeft = rand(5, $stockTotal);
        $sold = $stockTotal - $stockLeft;
        $progressPercentage = $stockTotal > 0 ? round(($sold / $stockTotal) * 100) : 0;

        // Get primary category with its actual data
        $primaryCategory = $product->categories->first();

        return [
            'id' => $this->id,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'url' => $product->url,
                'image' => $product->getFirstMediaUrl('displayImage'),
                'thumbnail' => $product->getFirstMediaUrl('displayImage'),
            ],
            'original_price' => $originalPrice,
            'sale_price' => $salePrice,
            'discount_amount' => $discountAmount,
            'discount_percentage' => $discountPercentage,
            'formatted' => [
                'original_price' => LaravelMoney::format($originalPrice),
                'sale_price' => LaravelMoney::format($salePrice),
                'discount_amount' => LaravelMoney::format($discountAmount),
            ],
            'category' => $primaryCategory ? [
                'id' => $primaryCategory->id,
                'name' => $primaryCategory->name,
                'url' => $primaryCategory->url,
                'image' => $primaryCategory->getFirstMediaUrl('displayImage'),
                'thumbnail' => $primaryCategory->getFirstMediaUrl('displayImage'),
            ] : null,
            'starts_at' => $this->starts_from?->toISOString(),
            'ends_at' => $this->ends_till?->toISOString(),
            'stock_total' => $stockTotal,
            'stock_left' => $stockLeft,
            'sold' => $sold,
            'progress_percentage' => $progressPercentage,
            'sale_info' => [
                'id' => $this->sale->id,
                'name' => $this->sale->name,
                'uuid' => $this->sale->uuid,
                'description' => $this->sale->description,
                'action_type' => $this->sale->action_type,
            ],
            'is_active' => $this->starts_from <= now() && $this->ends_till >= now(),
            'time_left_hours' => $this->ends_till ? max(0, now()->diffInHours($this->ends_till)) : 0,
        ];
    }
}
