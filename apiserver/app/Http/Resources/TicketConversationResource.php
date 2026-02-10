<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TicketConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $attachments = method_exists($this, 'getMedia')
            ? $this->getMedia('ticketConversationAttachment')->map(fn ($media) => $media->getFullUrl())->values()->all()
            : ($this->attachment ?? []);

        return [
            'message' => $this->message,
            'attachments' => $attachments,
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
