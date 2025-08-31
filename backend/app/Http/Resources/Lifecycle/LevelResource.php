<?php

namespace App\Http\Resources\Lifecycle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LevelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name'  => $this->name,
            'url'   => $this->url,
            'validate_years' => $this->validate_years,
            'team_member_limit' => $this->team_member_limit
        ];
    }
}
