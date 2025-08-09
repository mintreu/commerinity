<?php

namespace App\Http\Resources\Naukri;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NaukriResource extends NaukriIndexResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request),[
            'description'   => $this->description,
            'vacancy'       => $this->vacancy,
            'open_date'     => optional($this->open_date)->format('d/m/Y'),
            'close_date'    => optional($this->close_date)->format('d/m/Y'),
            'is_payable'    => $this->is_payable,
            'fees'          => $this->fees,

        ]);
    }
}
