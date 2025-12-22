<?php

namespace App\Http\Resources\Recruitment;

use App\Http\Resources\Geo\AddressIndexResource;
use App\Http\Resources\Naukri\NaukriIndexResource;
use App\Http\Resources\Transaction\TransactionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JobApplicationIndexResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request),[

            'status_feedback'   => $this->status_feedback,
            'isPaid'    => $this->is_paid,
            'guardian'  => $this->guardian_name,
            'educations'    => $this->educations,
            'skills'    => $this->skills,
            'experiences'   => $this->experiences,
            'reference_name'    => $this->reference_name,
            'reference_contact' => $this->reference_contact,

            'recruitment'   => NaukriIndexResource::make($this->whenLoaded('naukri')),
            'address'       => AddressIndexResource::make($this->whenLoaded('address')),
            'transaction'   => TransactionResource::make($this->whenLoaded('transaction'))
        ]);
    }
}
