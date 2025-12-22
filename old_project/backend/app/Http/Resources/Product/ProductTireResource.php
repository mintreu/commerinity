<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mintreu\LaravelMoney\LaravelMoney;

class ProductTireResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "min_quantity" =>  $this->min_quantity,
			"max_quantity" =>  $this->max_quantity,
			"wholesale_unit_quantity" =>  $this->wholesale_unit_quantity,
			"price" =>  LaravelMoney::format($this->price),
			"stock" =>  $this->stock,
			"in_stock" =>  $this->in_stock,
        ];
    }
}
