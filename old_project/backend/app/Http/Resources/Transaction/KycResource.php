<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KycResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
//            'uuid'          => $this->uuid,
            'user_type'     => $this->user_type->getLabel(),
            'company_name'  => $this->company_name,
            'company_type'  => $this->company_type,
            'has_tax'       => (bool) $this->has_tax,
            'gst'           => $this->gst,
            'pan'           => $this->pan,
            'aadhaar'       => $this->aadhaar,
            'utility_bills' => $this->utility_bills,
//            'kycable_type'  => $this->kycable_type,
//            'kycable_id'    => $this->kycable_id,
            'aadhaarImage' => $this->getFirstMediaUrl('aadhaarImage'),
            'panImage' => $this->getFirstMediaUrl('panImage'),
            'gstImage' => $this->getFirstMediaUrl('gstImage'),
        ];
    }
}
