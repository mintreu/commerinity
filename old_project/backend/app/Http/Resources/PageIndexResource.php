<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PageIndexResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'slug' => $this->slug,
            'prefix' => $this->prefix,
            'url' => $this->url,
            'title' => $this->title,
            'layout' => $this->layout,
            'status' => $this->status,
            'order' => $this->order,
        ];
    }
}
