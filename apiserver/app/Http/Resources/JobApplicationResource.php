<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Recruitment\JobApplication
 */
class JobApplicationResource extends JsonResource
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
            'guardian_name' => $this->guardian_name,

            // Status
            'status' => $this->status?->value,
            'status_label' => $this->status?->getLabel(),
            'status_color' => $this->status?->getColor(),
            'status_icon' => $this->status?->getIcon(),
            'status_feedback' => $this->status_feedback,

            // Payment
            'is_paid' => $this->is_paid,
            'amount' => $this->amount,
            'amount_formatted' => MoneyService::format($this->amount),
            'amount_in_rupees' => MoneyService::toRupees($this->amount),

            // Qualifications
            'educations' => $this->educations,
            'skills' => $this->skills,
            'experiences' => $this->experiences,

            // Reference
            'reference_name' => $this->reference_name,
            'reference_contact' => $this->reference_contact,

            // Computed properties
            'can_edit' => $this->can_edit,
            'can_withdraw' => $this->can_withdraw,
            'is_final' => $this->status?->isFinal() ?? false,

            // Related resources
            'recruitment' => new RecruitmentResource($this->whenLoaded('recruitment')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'transaction' => $this->when(
                $this->relationLoaded('transaction') && $this->transaction,
                fn () => [
                    'id' => $this->transaction->id,
                    'uuid' => $this->transaction->uuid,
                    'status' => $this->transaction->status?->value,
                    'verified' => $this->transaction->verified,
                ]
            ),

            // Timestamps
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'submitted_at_formatted' => $this->submitted_at?->format('d M Y, h:i A'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
