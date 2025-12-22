<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Product\ProductIndexResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mintreu\Toolkit\Traits\HasResourceSupport;

class OrderProductResource extends JsonResource
{
    use HasResourceSupport;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "quantity" => $this->quamtotu,
			"amount" => $this->amount,
            "discount" => $this->discount,
            "tax" => $this->tax,
            "total"  => $this->total,
            "has_tax" => $this->has_tax,
            'product'   => $this->resourceCollectionWhenLoadedAndNotEmpty('product',ProductIndexResource::class)
        ];
    }
}
