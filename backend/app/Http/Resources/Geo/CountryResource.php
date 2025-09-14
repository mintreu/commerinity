<?php

namespace App\Http\Resources\Geo;

use Illuminate\Http\Request;

class CountryResource extends CountryIndexResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request),[


            'states'    => StateResource::collection($this->whenLoaded('states'))
        ]);
    }
}
