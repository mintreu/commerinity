<?php

declare(strict_types=1);

namespace App\Http\Resources\Affiliate;

use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Affiliate\AffiliateFundTransaction
 */
final class AffiliateFundTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'type_label' => $this->type?->getLabel(),
            'amount' => $this->amount,
            'amount_formatted' => MoneyService::format($this->amount),
            'balance_after' => $this->balance_after,
            'balance_after_formatted' => MoneyService::format($this->balance_after ?? 0),
            'notes' => $this->notes,
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
