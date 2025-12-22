<?php

namespace App\Http\Resources\Helpdesk;

use Illuminate\Http\Resources\Json\JsonResource;

class HelpdeskConversationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'message'    => $this->message,
            'author'      => $this->whenLoaded('authorable', AuthorResource::make($this->authorable)),
            'created_at' => $this->created_at?->toIso8601String(),
            'attachment'  => $this->getMedia('ticketConversationAttachment')->map(fn($media) => $media->getFullUrl()),
        ];
    }
}
