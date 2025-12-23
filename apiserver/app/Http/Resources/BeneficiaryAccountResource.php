<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\BeneficiaryAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BeneficiaryAccount
 */
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
            'uuid' => $this->uuid,
            'type' => $this->type->value,
            'type_label' => $this->type->getLabel(),
            'is_bank' => $this->type->isBank(),
            'is_upi' => $this->type->isUpi(),

            // Bank account details (masked for security)
            'account_number_masked' => $this->masked_account_number,
            'ifsc_code' => $this->ifsc_code,
            'bank_name' => $this->bank_name,
            'branch_name' => $this->branch_name,

            // UPI details
            'upi_id' => $this->upi_id,

            // Common fields
            'holder_name' => $this->holder_name,
            'display_name' => $this->display_name,

            // Status
            'status' => $this->status->value,
            'status_label' => $this->status->getLabel(),
            'status_color' => $this->status->getColor(),
            'is_verified' => $this->isVerified(),
            'can_receive_payout' => $this->canReceivePayout(),

            // Flags
            'is_default' => $this->is_default,

            // Timestamps
            'verified_at' => $this->verified_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
