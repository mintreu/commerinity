<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
//            'description' => $this->description,
            'price' => $this->price,
            'reward_point' => $this->reward_point,
            'returnable' => $this->is_returnable,
            'views' => $this->view_count,
            'thumbnail' => $this->getFirstMediaUrl('displayImage'),
           // 'banner' => $this->getFirstMediaUrl('bannerImage'),
            'meta' => $this->meta_data
        ];
    }
}
