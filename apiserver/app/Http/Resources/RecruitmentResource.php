<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Recruitment\Recruitment
 */
class RecruitmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'role' => $this->role?->value,
            'role_label' => $this->role?->getLabel(),
            'location' => $this->location,
            'employment_type' => $this->employment_type?->value,
            'employment_type_label' => $this->employment_type?->getLabel(),
            'vacancy' => $this->vacancy,
            'open_date' => $this->open_date?->toIso8601String(),
            'close_date' => $this->close_date?->toIso8601String(),
            'open_date_formatted' => $this->open_date?->format('d M Y'),
            'close_date_formatted' => $this->close_date?->format('d M Y'),
            'is_payable' => $this->is_payable,
            'fees' => $this->fees ?? 0,
            'fees_formatted' => MoneyService::format($this->fees ?? 0),
            'fees_in_rupees' => MoneyService::toRupees($this->fees ?? 0),
            'requirements' => $this->requirements,
            'benefits' => $this->benefits,
            'eligibility' => $this->eligibility,
            'status' => $this->status?->value,
            'status_label' => $this->status?->getLabel(),

            // Computed properties
            'is_open' => $this->is_open,

            // Media
            'display_image' => $this->getFirstMediaUrl('display_image'),
            'info_pdf' => $this->getFirstMediaUrl('info_pdf'),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
