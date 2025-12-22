<?php

namespace App\Http\Resources\Geo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'             => $this->uuid,
            'title'            => $this->title,
            'type'             => $this->type->getLabel(),
            'address_1'        => $this->address_1,
            'city'             => $this->city,
            'postal_code'      => $this->postal_code,
            'default'          => $this->default,
            'village'          => $this->village,
            'priority'         => $this->priority,
            'latitude'         => $this->latitude,
            'longitude'        => $this->longitude,
            'block'            => BlockResource::make($this->whenLoaded('block')),
            'state'            => StateResource::make($this->whenLoaded('state')),
            'country'          => CountryResource::make($this->whenLoaded('country')),

        ];
    }
}
