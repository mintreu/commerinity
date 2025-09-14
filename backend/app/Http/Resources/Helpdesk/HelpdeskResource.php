<?php

namespace App\Http\Resources\Helpdesk;

use Illuminate\Http\Resources\Json\JsonResource;

class HelpdeskResource extends JsonResource
{
    public function toArray($request): array
    {

        return [
            'uuid'        => $this->uuid,
            'title'       => $this->title,
            'description' => $this->description,
            'priority'    => $this->priority->getLabel(),
            'status'      => $this->status->getLabel(),
            'topic'       => $this->whenLoaded('topic', HelpdeskTopicResource::make($this->topic)),
            'author'      => $this->whenLoaded('authorable', AuthorResource::make($this->authorable)),
            'created_at'  => $this->created_at?->toIso8601String(),
            'attachment'  => $this->getMedia('ticketAttachment')->map(fn($media) => $media->getFullUrl()),
        ];
    }
}
