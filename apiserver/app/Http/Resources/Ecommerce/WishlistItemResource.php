<?php

declare(strict_types=1);

namespace App\Http\Resources\Ecommerce;

use App\Http\Resources\ImageResource;
use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Ecommerce\ProductWishlist
 */
class WishlistItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->product;

        if (! $product) {
            return [
                'id' => $this->id,
                'is_available' => false,
            ];
        }

        $displayMedia = $product->getFirstMedia('displayImage');
        $price = $product->getPrice();

        return [
            'id' => $this->id,
            'added_at' => $this->created_at->toIso8601String(),
            'product' => [
                'name' => $product->name,
                'slug' => $product->url,
                'sku' => $product->sku,
                'price' => $price,
                'price_formatted' => MoneyService::format($price),
                'image' => $displayMedia ? (new ImageResource($displayMedia))->toArray($request) : null,
                'in_stock' => $product->total_stock > 0,
                'category' => $product->category ? new CategoryBriefResource($product->category) : null,
            ],
        ];
    }

}
