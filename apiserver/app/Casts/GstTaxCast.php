<?php

declare(strict_types=1);

namespace App\Casts;

use App\Services\MoneyService;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * GST Rate Cast (Filament v4 friendly)
 *
 * Practical slabs:
 * - Core: 0%, 5%, 18%, 40%
 * - Special (optional): 0.25% (rough precious stones), 3% (gold)
 *
 * Tax type (CGST/SGST vs IGST) is determined separately via GstTaxTypeCast.
 */
enum GstTaxCast: string implements HasColor, HasIcon, HasLabel
{
    case NONE = '0';

    // Special rates (keep only if you need them)
    case GST_0_25 = '0.25';
    case GST_3 = '3';

    // Core slabs
    case GST_5 = '5';
    case GST_18 = '18';
    case GST_40 = '40';

    public function getLabel(): string
    {
        return match ($this) {
            self::NONE => '0% GST (No Tax)',
            self::GST_0_25 => '0.25% GST (Special)',
            self::GST_3 => '3% GST (Special)',
            self::GST_5 => '5% GST',
            self::GST_18 => '18% GST',
            self::GST_40 => '40% GST (Luxury/Sin)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NONE => Color::Gray,
            self::GST_0_25 => Color::Sky,
            self::GST_3 => Color::Teal,
            self::GST_5 => Color::Blue,
            self::GST_18 => 'warning',
            self::GST_40 => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::NONE => 'heroicon-o-minus-circle',
            self::GST_0_25 => 'heroicon-o-sparkles',
            self::GST_3 => 'heroicon-o-currency-rupee',
            self::GST_5 => 'heroicon-o-receipt-refund',
            self::GST_18 => 'heroicon-o-receipt-percent',
            self::GST_40 => 'heroicon-o-fire',
        };
    }

    public function percentage(): float
    {
        return (float) $this->value;
    }

    /**
     * Calculate GST tax amount for a taxable base amount.
     * Pass your amount consistently (paisa OR rupees).
     */
    public function taxAmount(int|float $taxableAmount): float
    {
        $rate = $this->percentage();

        if ($rate <= 0) {
            return 0.0;
        }

        return ((float) $taxableAmount) * ($rate / 100);
    }

    /**
     * Format tax breakdown HTML for display.
     *
     * @param int|float $taxAmount The GST tax amount (NOT the base amount)
     */
    public function formatBreakdown(int|float $taxAmount, ?GstTaxTypeCast $taxType = null): HtmlString
    {
        $formattedTax = MoneyService::formatStatic($taxAmount);

        if ($this === self::NONE || ! $taxType || $taxType === GstTaxTypeCast::NONE) {
            return new HtmlString($formattedTax.' <small>(No GST)</small>');
        }

        $half = $taxAmount / 2;

        return match ($taxType) {
            GstTaxTypeCast::IGST => new HtmlString(
                $formattedTax.' <small>(IGST: '.$formattedTax.')</small>'
            ),

            GstTaxTypeCast::CGST_SGST => new HtmlString(
                $formattedTax.' <small>(CGST: '.MoneyService::formatStatic($half).', SGST: '.MoneyService::formatStatic($half).')</small>'
            ),

            GstTaxTypeCast::CGST_UTGST => new HtmlString(
                $formattedTax.' <small>(CGST: '.MoneyService::formatStatic($half).', UTGST: '.MoneyService::formatStatic($half).')</small>'
            ),

            default => new HtmlString($formattedTax.' <small>(No GST)</small>'),
        };
    }

    public function taxBreakdownHtml(
        int|float $taxableAmount,
        ?string $customerState,
        ?string $warehouseState
    ): HtmlString {
        $type = GstTaxTypeCast::determine($customerState, $warehouseState);
        $tax = $this->taxAmount($taxableAmount);

        return $this->formatBreakdown($tax, $type);
    }

    public static function determineTaxType(?string $customerState, ?string $warehouseState): GstTaxTypeCast
    {
        return GstTaxTypeCast::determine($customerState, $warehouseState);
    }
}

/**
 * GST Tax Type (place-of-supply style logic)
 *
 * Note: Real GST place-of-supply can be more nuanced (goods vs services),
 * but for most ecommerce goods use-cases: same-state => CGST+SGST/UTGST, else IGST.
 */
enum GstTaxTypeCast: string
{
    case CGST_SGST = 'CGST/SGST';
    case CGST_UTGST = 'CGST/UTGST';
    case IGST = 'IGST';
    case NONE = 'None';

    private const UNION_TERRITORIES = [
        'andaman and nicobar islands',
        'chandigarh',
        'dadra and nagar haveli and daman and diu',
        'delhi',
        'jammu and kashmir',
        'ladakh',
        'lakshadweep',
        'puducherry',
    ];

    public static function determine(?string $customerState, ?string $warehouseState): self
    {
        $customer = self::normalizeState($customerState);
        $warehouse = self::normalizeState($warehouseState);

        if (! $customer || ! $warehouse) {
            return self::NONE;
        }

        if ($customer === $warehouse) {
            return self::isUnionTerritory($customer)
                ? self::CGST_UTGST
                : self::CGST_SGST;
        }

        return self::IGST;
    }

    private static function normalizeState(?string $state): ?string
    {
        if (! $state) {
            return null;
        }

        $s = Str::of($state)
            ->lower()
            ->replace(['&', '.'], ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        $map = [
            'nct of delhi' => 'delhi',
            'new delhi' => 'delhi',
            'pondicherry' => 'puducherry',
            'orissa' => 'odisha',
            'dadra & nagar haveli and daman & diu' => 'dadra and nagar haveli and daman and diu',
        ];

        return $map[$s] ?? $s;
    }

    private static function isUnionTerritory(string $state): bool
    {
        return in_array($state, self::UNION_TERRITORIES, true);
    }
}
