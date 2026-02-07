<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum FundTransactionTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';

    public function getLabel(): string
    {
        return match ($this) {
            self::CREDIT => 'Credit',
            self::DEBIT => 'Debit',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CREDIT => 'success',
            self::DEBIT => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CREDIT => 'heroicon-o-plus-circle',
            self::DEBIT => 'heroicon-o-minus-circle',
        };
    }
}
