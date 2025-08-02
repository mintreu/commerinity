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
        return [
            'name' => $this->name,
            'url' => $this->url,
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'price' => LaravelMoney::format($this->price),
            'min_quantity' => $this->min_quantity,
            'max_quantity' => $this->max_quantity,
            'reward_point' => $this->reward_point,
            'returnable' => $this->is_returnable,
            'views' => $this->view_count,
            'thumbnail' => $this->getFirstMediaUrl('displayImage'),
           // 'banner' => $this->getFirstMediaUrl('bannerImage'),
            'meta' => $this->meta_data
        ];
    }
}
