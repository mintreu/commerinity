<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $priority = $this->priority;
        $status = $this->status;
        $attachments = method_exists($this, 'getMedia')
            ? $this->getMedia('ticketAttachment')->map(fn ($media) => $media->getFullUrl())->values()->all()
            : ($this->attachment ?? []);

        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => is_object($priority) && property_exists($priority, 'value') ? $priority->value : $priority,
            'status' => is_object($status) && property_exists($status, 'value') ? $status->value : $status,
            'attachments' => $attachments,
            'created_at' => $this->created_at->toISOString(),
            'topic' => $this->whenLoaded('topic', fn () => [
                'id' => $this->topic->id,
                'name' => $this->topic->name,
                'slug' => $this->topic->slug,
            ]),
            'author' => $this->whenLoaded('authorable', fn () => [
                'name' => $this->authorable->name ?? 'Unknown',
                'email' => $this->authorable->email ?? '',
                'fingerprint' => $this->authorable->fingerprint ?? '',
            ]),
        ];
    }
}
