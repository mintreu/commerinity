<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum GiftOptionCast: string implements HasLabel, HasIcon, HasColor
{
    case GIFT_WRAP = 'gift_wrap';
    case FESTIVE_WRAP = 'festive_wrap';
    case CUSTOM_MESSAGE = 'custom_message';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::GIFT_WRAP => 'Gift Wrap',
            self::FESTIVE_WRAP => 'Festive Wrap',
            self::CUSTOM_MESSAGE => 'Custom Message',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::GIFT_WRAP => 'heroicon-o-gift',
            self::FESTIVE_WRAP => 'heroicon-o-sparkles',
            self::CUSTOM_MESSAGE => 'heroicon-o-chat-bubble-left-right',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::GIFT_WRAP => 'primary',
            self::FESTIVE_WRAP => 'warning',
            self::CUSTOM_MESSAGE => 'info',
        };
    }
}
