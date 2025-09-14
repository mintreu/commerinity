<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Mintreu\LaravelMoney\LaravelMoney;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'  => $this->uuid,
            'balance'   => LaravelMoney::format($this->balance ?? 0),
            'transactions'   => $this->whenLoaded('transactions',TransactionResource::collection($this->transactions))
        ];
    }
}
