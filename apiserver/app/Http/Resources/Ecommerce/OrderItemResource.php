<?php

declare(strict_types=1);

namespace App\Http\Resources\Ecommerce;

use App\Http\Resources\ImageResource;
use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ecommerce\OrderItem
 */
class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->product;
        // In case product is deleted, we fallback.

        $displayMedia = $product?->getFirstMedia('displayImage');

        return [
            'id' => $this->uuid,
            'product_name' => $product?->name ?? $this->product_name ?? 'Product',
            'product_slug' => $product?->url ?? '',
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'unit_price_formatted' => MoneyService::format($this->unit_price),
            'subtotal' => $this->unit_price * $this->quantity,
            'subtotal_formatted' => MoneyService::format($this->unit_price * $this->quantity),
            'image' => $displayMedia ? (new ImageResource($displayMedia))->toArray($request) : null,
        ];
    }
}
