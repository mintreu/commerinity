<?php

declare(strict_types=1);

namespace App\Http\Resources\Rewards;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Rewards\RewardEarning
 */
final class RewardEarningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'reward_type' => $this->reward_type?->value,
            'reward_type_label' => $this->reward_type?->getLabel(),
            'coins' => $this->coins,
            'voucher_code' => $this->voucherCode?->code,
            'voucher_uuid' => $this->voucherCode?->uuid,
            'status' => $this->status?->value,
            'status_label' => $this->status?->getLabel(),
            'is_used' => $this->is_used,
            'claimed_at' => $this->claimed_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
