<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AppointmentParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'role' => $this->role,
            'user' => [
                'uuid' => $this->user?->uuid,
                'name' => $this->user?->name,
                'type' => $this->user?->type?->value,
            ]
        ];
    }
}
