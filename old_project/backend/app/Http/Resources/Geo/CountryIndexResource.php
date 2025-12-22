<?php

namespace App\Http\Resources\Geo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "name" => $this->name,
			"code" => $this->iso_code_2,
            "isd_code" => $this->isd_code,
            "locale" => $this->locale,
            "timezone" => $this->timezone,
			"timezone_diff" => $this->timezone_diff,
            "currency" => $this->currency,
            "flag" => $this->flag,
            "region" => $this->region,
        ];
    }
}
