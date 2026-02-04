<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ChallengeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'reward' => [
                'type' => $this->reward_type,
                'value' => $this->reward_value,
            ],
            'goal' => [
                'type' => $this->goal_type,
                'value' => $this->goal_value,
            ],
            'target_user_type' => $this->target_user_type,
            'targetable' => $this->when($this->targetable, fn () => [
                'type' => class_basename($this->targetable_type),
                'uuid' => $this->targetable?->uuid,
                'label' => $this->targetable?->name ?? $this->targetable?->title ?? null,
            ]),
            'target_level' => $this->when($this->targetLevel, fn () => [
                'uuid' => $this->targetLevel->uuid,
                'name' => $this->targetLevel->name,
            ]),
            'target_stage' => $this->when($this->targetStage, fn () => [
                'uuid' => $this->targetStage->uuid,
                'name' => $this->targetStage->name,
            ]),
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
