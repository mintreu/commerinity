<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isDefaultPin = $this->pin ? Hash::check('123456', $this->pin) : false;

        return [
            'uuid' => $this->uuid,
            'balance' => $this->balance,
            'balance_formatted' => MoneyService::format($this->balance),
            'hold_balance' => $this->hold_balance,
            'hold_balance_formatted' => MoneyService::format($this->hold_balance),
            'available_balance' => $this->available_balance,
            'available_balance_formatted' => MoneyService::format($this->available_balance),
            'total_credited' => $this->total_credited,
            'total_credited_formatted' => MoneyService::format($this->total_credited),
            'total_debited' => $this->total_debited,
            'total_debited_formatted' => MoneyService::format($this->total_debited),
            'points' => $this->points,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'can_transact' => $this->canTransact(),
            'can_receive' => $this->canReceive(),
            'has_pin' => $this->hasPin() && ! $isDefaultPin,
            'requires_pin_setup' => ! $this->hasPin() || $isDefaultPin,
            'pin_updated_at' => $this->pin_updated_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
