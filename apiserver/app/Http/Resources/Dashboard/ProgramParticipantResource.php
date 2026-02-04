<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProgramParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'role' => $this->role,
            'status' => $this->status,
            'joined_at' => $this->joined_at?->toIso8601String(),
            'invited_by' => $this->when($this->inviter, fn () => [
                'uuid' => $this->inviter->uuid,
                'name' => $this->inviter->name,
            ]),
            'user' => $this->when($this->user, fn () => [
                'uuid' => $this->user->uuid,
                'name' => $this->user->name,
                'type' => $this->user->type?->value,
            ]),
        ];
    }
}
