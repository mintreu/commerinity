<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'person_name' => $this->person_name,
            'person_email' => $this->person_email,
            'person_mobile' => $this->person_mobile,
            'alternate_contact' => $this->alternate_contact,
            'type' => $this->type,
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'landmark' => $this->landmark,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'default' => $this->default,
            'priority' => $this->priority,
            'pickup_location' => $this->pickup_location,
            'full_address' => $this->full_address,
            'country' => [
                'code' => $this->country?->iso_code_2,
                'name' => $this->country?->name,
            ],
            'state' => [
                'code' => $this->state?->code,
                'name' => $this->state?->name,
            ],
            'block' => $this->whenLoaded('block', [
                'id' => $this->block?->id,
                'name' => $this->block?->name,
                'district' => $this->block?->district,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
