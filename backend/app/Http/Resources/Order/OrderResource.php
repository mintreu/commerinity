<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Geo\AddressResource;
use App\Http\Resources\Transaction\TransactionResource;
use Illuminate\Http\Request;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\Toolkit\Traits\HasResourceSupport;

class OrderResource extends OrderIndexResource
{

    use HasResourceSupport;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request),[
            'amount'    => LaravelMoney::format($this->amount),
            'subtotal'  => LaravelMoney::format($this->subtotal),
            'discount'  => LaravelMoney::format($this->discount),
            'tax'   => LaravelMoney::format($this->tax),
            'total' => LaravelMoney::format($this->total),
            'is_cod'    => $this->is_cod,
            'is_paid'   => $this->payment_success,
            'expire_at' => $this->expire_at->diffForHumans(),

            'tracking_id'   => $this->tracking_id,
            'voucher'       => $this->voucher,
            'products'  => $this->resourceCollectionWhenLoadedAndNotEmpty('orderProducts', OrderProductResource::class),
            'transaction' => $this->whenLoaded('transaction',TransactionResource::make($this->transaction)),
            'address' => [
                'billing' => $this->whenLoaded('billingAddress',AddressResource::make($this->billingAddress)),
                'shipping' => $this->whenLoaded('shippingAddress',AddressResource::make($this->shippingAddress)),
            ],

        ]);
    }




//    private function resourceCollectionWhenLoadedAndNotEmpty($relation, $resourceClass)
//    {
//        return $this->when(
//            $this->relationLoaded($relation) && $this->$relation->isNotEmpty(),
//            fn () => $resourceClass::collection($this->$relation)
//        );
//    }






}
