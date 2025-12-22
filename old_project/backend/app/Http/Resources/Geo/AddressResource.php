<?php

namespace App\Http\Resources\Geo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'person_name'      => $this->person_name,
            'person_email'     => $this->person_email,
            'person_mobile'    => $this->person_mobile,
            'alternate_contact'=> $this->alternate_contact,
            'landmark'         => $this->landmark,
            'pickup_location'  => $this->pickup_location,
            'priority'         => $this->priority,
            'addressable_type' => $this->addressable_type,
            'addressable_id'   => $this->addressable_id,
            'latitude'         => $this->latitude,
            'longitude'        => $this->longitude,
            'block_id'         => $this->block_id,
            'state_code'       => $this->state_code,
            'country_code'     => $this->country_code,

            'block'            => BlockResource::make($this->whenLoaded('block')),
            'state'            => StateResource::make($this->whenLoaded('state')),
            'country'            => CountryResource::make($this->whenLoaded('country')),

        ];
    }
}
