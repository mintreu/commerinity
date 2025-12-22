<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeneficiaryAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'                   => $this->uuid,
            'upi_handle'             => $this->upi_handle,
            'ifsc'                   => $this->ifsc,
            'bank_name'              => $this->bank_name,
            'bank_branch'            => $this->bank_branch,
            'account_name'           => $this->account_name,
            'account_number'         => $this->account_number,
            'type'                   => $this->type->getLabel(),
            'default'                => (bool) $this->default,
            'status'                 => $this->status->getLabel(),
            'status_feedback'        => $this->status_feedback,
            'beneficiary_active'     => (bool) $this->beneficiary_active,
        ];
    }
}
