<?php

declare(strict_types=1);

namespace App\Http\Resources\Affiliate;

use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Affiliate\AffiliateFundAccount
 */
final class AffiliateFundAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fund_type' => $this->fund_type,
            'balance' => $this->balance,
            'balance_formatted' => MoneyService::format($this->balance),
            'total_credited' => $this->total_credited,
            'total_credited_formatted' => MoneyService::format($this->total_credited),
            'total_debited' => $this->total_debited,
            'total_debited_formatted' => MoneyService::format($this->total_debited),
            'is_active' => $this->is_active,
        ];
    }
}
