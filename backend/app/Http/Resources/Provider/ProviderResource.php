<?php

namespace App\Http\Resources\Provider;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
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
            'default' => $this->default,
            'type' => $this->type->getLabel(),
            'hasCharge' => $this->hasCharge(),
            'charge' => $this->charge,
            'thumbnail' => $this->getFirstMediaUrl('displayImage')
        ];
    }
}
