<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\ImageResource;
use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Cart
 */
final class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->cartable;
        $price = $product?->getDisplayPrice() ?? 0;
        $mrp = (int) (($product?->base_price ?: $product?->getPrice()) ?? 0);
        $originalPrice = $mrp > $price ? $mrp : null;

        return [
            'product_slug' => $product?->url,
            'name' => $product?->name,
            'sku' => $product?->sku,
            'quantity' => $this->quantity,
            'price' => $price,
            'price_formatted' => MoneyService::format($price),
            'mrp' => $mrp,
            'mrp_formatted' => MoneyService::format($mrp),
            'original_price' => $originalPrice,
            'original_price_formatted' => $originalPrice ? MoneyService::format($originalPrice) : null,
            'subtotal' => $price * $this->quantity,
            'subtotal_formatted' => MoneyService::format($price * $this->quantity),
            'image' => $product?->getFirstMedia('displayImage') ? (new ImageResource($product->getFirstMedia('displayImage')))->toArray(request()) : null,
        ];
    }

}
