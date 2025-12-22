<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductEngagementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'review' => $this->review,
            'rating' => $this->rating,
            'helpful_votes' => $this->helpful_votes ?? 0,
            'author' => [
                'name' => $this->authorable?->name ?? 'Anonymous',
                'type' => $this->authorable_type,
            ],


            'product' => [
                'url' => $this->product?->url,
                'sku' => $this->product?->sku,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
