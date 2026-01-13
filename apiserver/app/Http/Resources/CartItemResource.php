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

        return [
            'product_slug' => $product?->url,
            'name' => $product?->name,
            'sku' => $product?->sku,
            'quantity' => $this->quantity,
            'price' => $product?->price ?? 0,
            'price_formatted' => MoneyService::format($product?->price ?? 0),
            'subtotal' => ($product?->price ?? 0) * $this->quantity,
            'subtotal_formatted' => MoneyService::format(($product?->price ?? 0) * $this->quantity),
            'image' => $product?->getFirstMedia('displayImage') ? (new ImageResource($product->getFirstMedia('displayImage')))->toArray(request()) : null,
        ];
    }
}
