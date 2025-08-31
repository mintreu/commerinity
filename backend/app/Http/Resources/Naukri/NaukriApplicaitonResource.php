<?php

namespace App\Http\Resources\Naukri;

use App\Http\Resources\Geo\AddressIndexResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NaukriApplicaitonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'  => $this->uuid,
            'guardian'  => $this->guardian_name,
            'isPaid'    => $this->is_paid,
            'submit_on' => $this->submitted_at,
            'educations'    => $this->educations,
            'skills'    => $this->skills,
            'experiences'   => $this->experiences,
            'reference_name'    => $this->reference_name,
            'reference_contact' => $this->reference_contact,
            'status'    => $this->status->getLabel(),
            'status_feedback'   => $this->status_feedback,
            'recruitment'   => NaukriIndexResource::make($this->whenLoaded('naukri')),
            'address'       => AddressIndexResource::make($this->whenLoaded('address')),


        ];
    }
}
