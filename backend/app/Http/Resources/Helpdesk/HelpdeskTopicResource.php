<?php

namespace App\Http\Resources\Helpdesk;


use Illuminate\Http\Resources\Json\JsonResource;

class HelpdeskTopicResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name'        => $this->name,
            'slug'        => $this->slug,
            'order'       => (int) $this->order,
        ];
    }
}

