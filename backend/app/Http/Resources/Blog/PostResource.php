<?php

namespace App\Http\Resources\Blog;

use Illuminate\Http\Request;

class PostResource extends PostIndexResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request),[
            'banner' => $this->getFirstMediaUrl('bannerImage'),
            'description' => $this->description,
        ]);
    }
}
