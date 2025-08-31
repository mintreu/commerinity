<?php

namespace Mintreu\LaravelIntegration\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationResource extends JsonResource
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
            'thumbnail' => $this->logo_url ?? null
        ];
    }
}
