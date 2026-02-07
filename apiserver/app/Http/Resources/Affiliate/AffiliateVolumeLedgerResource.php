<?php

declare(strict_types=1);

namespace App\Http\Resources\Affiliate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Affiliate\AffiliateVolumeLedger
 */
final class AffiliateVolumeLedgerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status?->value,
            'status_label' => $this->status?->getLabel(),
            'bv' => $this->bv,
            'pv' => $this->pv,
            'depth' => $this->depth,
            'order_id' => $this->order_id,
            'order_item_id' => $this->order_item_id,
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'eligible_at' => $this->eligible_at?->toIso8601String(),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'reversed_at' => $this->reversed_at?->toIso8601String(),
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
