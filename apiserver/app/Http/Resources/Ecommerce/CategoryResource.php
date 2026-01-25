<?php

declare(strict_types=1);

namespace App\Http\Resources\Ecommerce;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'url' => $this->url,
            'status' => $this->status,
            'desc' => $this->desc,
            'view_count' => $this->view_count,
            'order' => $this->order,
            'children_count' => $this->children()->count(),
            'products_count' => $this->whenLoaded('products', fn () => $this->products_count ?? $this->products()->count()),
            'thumbnail' => $this->getFirstMediaUrl('thumbnail', 'thumb'),
            'banner' => $this->getFirstMediaUrl('banner'),
            'seo_meta' => $this->seo_meta,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
