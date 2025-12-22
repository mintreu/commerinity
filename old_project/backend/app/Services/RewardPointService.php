<?php

namespace App\Services;

use Mintreu\LaravelProductCatalogue\Models\ProductTier;
use Mintreu\LaravelMoney\LaravelMoney;

class RewardPointService
{
    public function calculate(?ProductTier $tier = null): float|int
    {
        if ($tier) {
            $profitMarginPercentage = config('laravel-product-catalogue.reward.profit_margin_percentage');
            $conversion = config('laravel-product-catalogue.reward.conversion');

            if (is_null($profitMarginPercentage) || is_null($conversion) || $conversion == 0) {
                return 0;
            }

            $reward = LaravelMoney::make($tier->profit_margin)
                ->multiply($profitMarginPercentage)
                ->divide(100);

            return $reward->divide($conversion)->getAmount();
        }

        return 0;
    }
}
