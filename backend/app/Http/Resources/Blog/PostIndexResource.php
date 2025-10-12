<?php

namespace App\Http\Resources\Blog;

use App\Http\Resources\Category\CategoryIndexResource;
use App\Http\Resources\Helpdesk\AuthorResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostIndexResource extends JsonResource
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
            'updated_at' => $this->updated_at,
            'thumbnail' => $this->getFirstMediaUrl('displayImage'),
            'category' => $this->whenLoaded('category',CategoryIndexResource::make($this->category)),
            'author' => $this->whenLoaded('author',AuthorResource::make($this->author)),
        ];
    }
}
