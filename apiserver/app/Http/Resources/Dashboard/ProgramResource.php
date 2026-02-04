<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $location = $this->address;

        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'creator' => $this->when($this->creator, fn () => [
                'type' => class_basename($this->creator_type),
                'uuid' => $this->creator?->uuid,
                'name' => $this->creator?->name,
            ]),
            'location' => $location ? [
                'uuid' => $location->uuid,
                'title' => $location->title,
                'full_address' => $location->full_address,
                'city' => $location->city,
                'state' => $location->state?->name,
                'country' => $location->country?->name,
            ] : null,
            'participants' => ProgramParticipantResource::collection($this->whenLoaded('participants') ?? $this->participants),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
