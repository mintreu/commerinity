<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return parent::toArray($request);

        return [
            'name' => $this->name,
            'url' => $this->url,
            'views' => $this->view_count,
            'thumbnail' => $this->getFirstMediaUrl('displayImage'),
            'meta' => $this->meta_data
        ];

    }
}
