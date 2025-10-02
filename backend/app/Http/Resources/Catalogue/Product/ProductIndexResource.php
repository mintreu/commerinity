<?php

namespace App\Http\Resources\Catalogue\Product;

use App\Http\Resources\Category\CategoryIndexResource;
use App\Http\Resources\Product\ProductTireResource;
use App\Http\Resources\Promotion\SaleIndexResource;
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
       // return parent::toArray($request);


        return [
            'name'  => $this->name,
            'url'   => $this->url,
            'sku'   => $this->sku,
            'type'  => $this->type->getLabel(),
            'views' => $this->view_count,
            'has_stock' => $this->has_stock,
            'rating' => $this->engagements_avg_rating,
            'review' => $this->review_count,
            'review_avg_helpful_votes' => $this->engagements_avg_helpful_votes,
            'reward_point' => $this->reward_point,
            'is_wishlisted' => $this->is_wishlisted,
            'price' => LaravelMoney::format($this->price),
            'sales' => $this->whenLoaded('sales',SaleIndexResource::collection($this->sales)),
            'categories' => $this->whenLoaded('categories',CategoryIndexResource::collection($this->categories)),
            'thumbnail' => $this->getFirstMediaUrl('displayImage'),
            'tire' => $this->whenLoaded('cheapestTier',ProductTireResource::make($this->cheapestTier))
        ];
    }
}
