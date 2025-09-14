<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mintreu\LaravelMoney\LaravelMoney;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'uuid'  => $this->uuid,
            'description'   => $this->description,
            'discount'  => in_array($this->action_type,['to_fixed','by_fixed']) ?  LaravelMoney::format($this->discount_amount) : $this->discount_amount,
            'condition' => $this->conditions,
            'discount_type' => $this->action_type
        ];
    }





}
