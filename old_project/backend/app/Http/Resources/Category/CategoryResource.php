<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'views' => $this->view_count,
            'thumbnail' => $this->getFirstMediaUrl('displayImage'),
            'banner' => $this->getFirstMediaUrl('bannerImage'),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'descendents' => CategoryResource::collection($this->whenLoaded('descendents')),
            'meta' => $this->meta_data
        ];
    }





}
