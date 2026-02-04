<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Dashboard\AppointmentParticipantResource;

final class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'agenda' => $this->agenda,
            'meeting_mode' => $this->meeting_mode,
            'meeting_link' => $this->meeting_link,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'creator' => $this->when($this->creator, fn () => [
                'type' => class_basename($this->creator_type),
                'uuid' => $this->creator?->uuid,
                'name' => $this->creator?->name,
            ]),
            'advisor' => $this->when($this->advisor, fn () => [
                'uuid' => $this->advisor->uuid,
                'name' => $this->advisor->name,
            ]),
            'mentor' => $this->when($this->mentor, fn () => [
                'uuid' => $this->mentor->uuid,
                'name' => $this->mentor->name,
            ]),
            'attendee' => $this->attendee ? [
                'uuid' => $this->attendee->uuid,
                'name' => $this->attendee->name,
            ] : null,
            'participants' => AppointmentParticipantResource::collection($this->whenLoaded('participants') ?? $this->participants),
        ];
    }
}
