<?php

namespace App\Http\Resources\Lifecycle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mintreu\LaravelMoney\LaravelMoney;

class StageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name'  => $this->name,
            'url'   =>  $this->url,
            'description'   => $this->desc,
            'price' => LaravelMoney::format($this->price),
            'max_team_capacity' => $this->max_team_members,
            'benefits' => $this->benefits,
            'accessibility' => $this->accessibility,
            'levels' => LevelResource::collection($this->whenLoaded('levels'))
        ];
    }
}
