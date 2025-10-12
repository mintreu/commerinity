<?php

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TaxTypeCast: string implements HasLabel, HasIcon, HasColor
{
    case GOODS = 'goods';
    case SERVICES = 'services';
    case EXEMPT = 'exempt';
    case NIL_RATED = 'nil_rated';
    case ZERO_RATED = 'zero_rated';
    case NON_GST = 'non_gst';
    case COMPOSITE = 'composite';
    case MIXED = 'mixed';
    case REVERSE_CHARGE = 'reverse_charge';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::GOODS => 'Goods',
            self::SERVICES => 'Services',
            self::EXEMPT => 'Exempt Supply',
            self::NIL_RATED => 'Nil Rated',
            self::ZERO_RATED => 'Zero Rated (Export/SEZ)',
            self::NON_GST => 'Non-GST Supply',
            self::COMPOSITE => 'Composite Supply',
            self::MIXED => 'Mixed Supply',
            self::REVERSE_CHARGE => 'Reverse Charge Supply',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::GOODS => 'heroicon-o-cube',
            self::SERVICES => 'heroicon-o-briefcase',
            self::EXEMPT => 'heroicon-o-shield-check',
            self::NIL_RATED => 'heroicon-o-currency-rupee',
            self::ZERO_RATED => 'heroicon-o-globe-alt',
            self::NON_GST => 'heroicon-o-no-symbol',
            self::COMPOSITE => 'heroicon-o-collection',
            self::MIXED => 'heroicon-o-squares-2x2',
            self::REVERSE_CHARGE => 'heroicon-o-arrow-path-rounded-square',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::GOODS => 'primary',
            self::SERVICES => 'info',
            self::EXEMPT => 'success',
            self::NIL_RATED => 'warning',
            self::ZERO_RATED => 'indigo',
            self::NON_GST => 'gray',
            self::COMPOSITE => 'purple',
            self::MIXED => 'pink',
            self::REVERSE_CHARGE => 'danger',
        };
    }
}
