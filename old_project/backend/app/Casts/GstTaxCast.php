<?php

namespace App\Casts;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Mintreu\LaravelMoney\LaravelMoney;

enum GstTaxCast: string implements HasLabel
{
    private const GST_TYPE_CGST_SGST = 'CGST/SGST';
    private const GST_TYPE_CGST_UTGST = 'CGST/UTGST';
    private const GST_TYPE_IGST = 'IGST';
    private const GST_TYPE_NONE = 'None';

    private const UNION_TERRITORY_STATES = [
        'andaman and nicobar islands',
        'chandigarh',
        'dadra and nagar haveli and daman and diu',
        'dadra and nagar haveli',
        'daman and diu',
        'delhi',
        'lakshadweep',
        'puducherry',
        'jammu and kashmir',
        'ladakh',
    ];

    case NONE = '0';
    case GST_5 = '5';
    case GST_12 = '12';
    case GST_18 = '18';
    case GST_28 = '28';
    case GST_40 = '40'; // GST 2.0 - Luxury goods rate (effective Sept 22, 2025)

    public function getLabel(): string
    {
        return match ($this) {
            self::NONE => '0% GST (NO TAX)',
            self::GST_5 => '5% GST',
            self::GST_12 => '12% GST',
            self::GST_18 => '18% GST',
            self::GST_28 => '28% GST',
            self::GST_40 => '40% GST (Luxury)',
        };
    }

    public function getFullLabel(): string
    {
        return match ($this) {
            self::NONE => '0% • Tax Exempt / Zero Rated',
            self::GST_5 => '5% • Essential Goods / Food / Healthcare',
            self::GST_12 => '12% • Standard Rate (Electronics, Clothing)',
            self::GST_18 => '18% • General GST (Services & Retail)',
            self::GST_28 => '28% • Premium / Luxury / Tobacco / SUVs',
            self::GST_40 => '40% • Luxury Goods (GST 2.0 • Effective Sept 2025)',
        };
    }


    public function percentage(): int
    {
        return match ($this) {
            self::NONE => 0,
            self::GST_5 => 5,
            self::GST_12 => 12,
            self::GST_18 => 18,
            self::GST_28 => 28,
            self::GST_40 => 40,
        };
    }

    public static function determineTaxType(?string $customerState, ?string $warehouseState): string
    {
        $customer = self::normalizeState($customerState);
        $warehouse = self::normalizeState($warehouseState);

        if (! $customer || ! $warehouse) {
            return self::GST_TYPE_NONE;
        }

        if ($customer === $warehouse) {
            return self::isUnionTerritory($customer)
                ? self::GST_TYPE_CGST_UTGST
                : self::GST_TYPE_CGST_SGST;
        }

        return self::GST_TYPE_IGST;
    }

    private static function normalizeState(?string $state): ?string
    {
        return $state ? Str::of($state)->lower()->trim()->__toString() : null;
    }

    private static function isUnionTerritory(string $state): bool
    {
        return in_array($state, self::UNION_TERRITORY_STATES, true);
    }

    public function formatBreakdown($amount): HtmlString
    {
        $formattedAmount = LaravelMoney::format($amount);

        if ($this === self::NONE) {
            return new HtmlString($formattedAmount.' <small>(None)</small>');
        }

        if (str_starts_with($this->value, 'cs')) {
            $halfTax = $amount / 2;

            return new HtmlString($formattedAmount.' <small>(CGST: '.LaravelMoney::format($halfTax).', SGST: '.LaravelMoney::format($halfTax).')</small>');
        }

        if (str_starts_with($this->value, 'i')) {
            return new HtmlString($formattedAmount.' <small>(IGST: '.$formattedAmount.')</small>');
        }

        return new HtmlString($formattedAmount.' <small>(None)</small>');
    }


}
