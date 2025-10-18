<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mintreu\LaravelMoney\LaravelMoney;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       // return parent::toArray($request);

        return [
            'uuid' => $this->uuid,
            'type' => $this->type->getLabel(),
            'purpose' => $this->purpose,
            'amount' => LaravelMoney::format($this->amount),
            'verified' => $this->verified,
            'status' => $this->status->getLabel(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'expire_at' => $this->expire_at,

            // View Purpose mandatory
            'provider_transaction_id' => $this->provider_transaction_id,
            'integration' => $this->integration->name
        ];
    }
}
