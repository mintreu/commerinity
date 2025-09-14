<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mintreu\LaravelMoney\LaravelMoney;

class SaleProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'start_from'    => $this->start_from,
            'ends_till'     => $this->ends_till,
            'sale_price'    => LaravelMoney::format($this->sale_price ?? 0),
            'discount'  => in_array($this->action_type,['to_fixed','by_fixed']) ?  LaravelMoney::format($this->discount_amount) : $this->discount_amount,
            'discount_type' => $this->action_type,
            'sale'     => $this->resourceCollectionWhenLoadedAndNotEmpty('sale',SaleResource::class),
        ];
    }



    private function resourceCollectionWhenLoadedAndNotEmpty($relation, $resourceClass)
    {
        return $this->when(
            $this->relationLoaded($relation) && !is_null($this->$relation),
            fn () => $resourceClass::make($this->$relation)
        );
    }

}
