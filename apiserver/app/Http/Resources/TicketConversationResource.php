<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TicketConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'attachment' => $this->attachment,
            'created_at' => $this->created_at->toISOString(),
            'author' => $this->whenLoaded('authorable', fn () => [
                'name' => $this->authorable->name ?? 'Unknown',
                'email' => $this->authorable->email ?? '',
                'fingerprint' => $this->authorable->fingerprint ?? '',
                'avatar' => $this->authorable->avatar ?? null,
            ]),
        ];
    }
}
