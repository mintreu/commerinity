<?php

namespace App\Http\Resources\Lifecycle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'      => $this->uuid,
            'amount'    =>  $this->amount,
            'is_paid'   => $this->is_paid,
            'expire_at' => $this->expire_at,
            'expired'   => now()->greaterThan($this->expire_at),
            'level_id'  => $this->level_id,
            'stage_id'  => $this->stage_id,
            'level'     => LevelResource::make($this->whenLoaded('level')),
            'stage'     => StageResource::make($this->whenLoaded('stage')),
        ];
    }
}
