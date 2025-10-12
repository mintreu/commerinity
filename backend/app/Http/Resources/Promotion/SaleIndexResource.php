<?php

namespace App\Http\Resources\Promotion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Mintreu\LaravelMoney\LaravelMoney;


class SaleIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'sale_price' => LaravelMoney::format($this->sale_price),
            'discount'  => LaravelMoney::format($this->discount_amount),
            'ends_till'  => $this->ends_till, // fixed typo
            // Remaining time in human-readable format
            'remaining'  => $this->ends_till
                ? Carbon::parse($this->ends_till)->diffForHumans(now(), [
                    'parts' => 3,      // show up to 3 time parts
                    'short' => true,   // "2d 5h" instead of "2 days 5 hours"
                    'syntax' => Carbon::DIFF_RELATIVE_TO_NOW, // "in 2d" not "2d ago"
                ])
                : null,

            'action_type' => $this->action_type->value,



        ];
    }





}
