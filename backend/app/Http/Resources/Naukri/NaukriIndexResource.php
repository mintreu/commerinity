<?php

namespace App\Http\Resources\Naukri;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NaukriIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name'  => $this->name,
            'url'   => $this->url,
            'role'  => ucwords($this->role ?? ''),
            'type'  => $this->employment_type->getLabel(),
            'location'  => $this->location,
            'thumbnail' => $this->getFirstMediaUrl('displayImage'),
            'pdf'       => $this->getFirstMediaUrl('infoPdf'),
        ];
    }
}
