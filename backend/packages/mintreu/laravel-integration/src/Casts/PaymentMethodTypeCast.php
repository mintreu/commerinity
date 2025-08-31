<?php

namespace Mintreu\LaravelIntegration\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethodTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case QR = 'qr';
    case STANDARD = 'standard';
    case LINK = 'link';

    /**
     * @return string|array|null
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::QR => 'info',
            self::STANDARD => 'success',
            self::LINK => 'warning',
        };
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return match ($this) {
            self::QR => 'heroicon-o-qr-code',
            self::STANDARD => 'heroicon-o-credit-card',
            self::LINK => 'heroicon-o-link',
        };
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::QR => 'QR Payment',
            self::STANDARD => 'Standard Payment',
            self::LINK => 'Payment Link',
        };
    }
}
