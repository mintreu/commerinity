<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type->value,
            'type_label' => $this->type->getLabel(),
            'status' => $this->status->value,
            'status_label' => $this->status->getLabel(),
            'amount' => $this->amount,
            'amount_formatted' => MoneyService::format($this->amount),
            'fee' => $this->fee,
            'fee_formatted' => MoneyService::format($this->fee),
            'tax' => $this->tax,
            'tax_formatted' => MoneyService::format($this->tax),
            'net_amount' => $this->net_amount,
            'net_amount_formatted' => MoneyService::format($this->net_amount),
            'currency' => $this->currency,
            'payment_method' => $this->payment_method?->value,
            'purpose' => $this->purpose,
            'description' => $this->description,
            'reference_number' => $this->reference_number,
            'balance_after' => $this->balance_after,
            'balance_after_formatted' => MoneyService::format($this->balance_after ?? 0),
            'is_verified' => $this->verified,
            'is_positive' => $this->type->isPositive(),
            'formatted_amount' => $this->formatted_amount,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
