<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class KycResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'kyc_type' => $this->kyc_type,
            'company_name' => $this->company_name,
            'company_type' => $this->company_type,
            'pan_number' => $this->pan_number,
            'aadhaar_number' => $this->aadhaar_number ? '********'.substr($this->aadhaar_number, -4) : null,
            'gst_number' => $this->gst_number,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'documents' => $this->whenLoaded('media', function () {
                return $this->getMedia('documents')->map(fn ($media) => [
                    'id' => $media->id,
                    'name' => $media->file_name,
                    'url' => url($media->getUrl()),
                    'size' => $media->size,
                    'mime_type' => $media->mime_type,
                ]);
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
