<?php

namespace App\Http\Resources\Helpdesk;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class AuthorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'fingerprint' => $this->resource->currentFingerprint(),
            'avatar' => $this->getFirstMediaUrl('avatarImage'),
        ];
    }
}
