<?php

declare(strict_types=1);

namespace App\Http\Resources\Affiliate;

use App\Services\MoneyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Affiliate\AffiliatePayout $resource
 */
final class AffiliatePayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->resource->uuid,
            'period_start' => $this->resource->period_start,
            'period_end' => $this->resource->period_end,
            'pv' => $this->resource->pv,
            'bv' => $this->resource->bv,
            'gross_amount' => $this->resource->gross_amount,
            'gross_amount_formatted' => MoneyService::format($this->resource->gross_amount),
            'platform_fee' => $this->resource->platform_fee,
            'platform_fee_formatted' => MoneyService::format($this->resource->platform_fee),
            'platform_fee_gst' => $this->resource->platform_fee_gst,
            'platform_fee_gst_formatted' => MoneyService::format($this->resource->platform_fee_gst),
            'tds_amount' => $this->resource->tds_amount,
            'tds_amount_formatted' => MoneyService::format($this->resource->tds_amount),
            'tcs_amount' => $this->resource->tcs_amount,
            'tcs_amount_formatted' => MoneyService::format($this->resource->tcs_amount),
            'net_amount' => $this->resource->net_amount,
            'net_amount_formatted' => MoneyService::format($this->resource->net_amount),
            'status' => $this->resource->status->value,
            'paid_at' => $this->resource->paid_at,
        ];
    }
}
