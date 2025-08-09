<?php

namespace Mintreu\LaravelGeokit\Casts;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AddressTypeCast: string implements HasLabel, HasIcon, HasColor
{
    case HOME = 'home';
    case WORK = 'work';
    case OTHER = 'other';
    case DELIVERY = 'delivery';
    case PICKUP = 'pickup';
    case HUB = 'hub';
    case SERVICE_POINT = 'service-point';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::HOME => 'success',
            self::WORK => Color::Emerald,
            self::OTHER => Color::Sky,
            self::DELIVERY => Color::Amber,
            self::PICKUP => Color::Blue,
            self::HUB => Color::Purple,
            self::SERVICE_POINT => Color::Red,
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::HOME => 'heroicon-s-user',
            self::WORK => 'heroicon-o-user',
            self::OTHER => 'heroicon-s-no-symbol',
            self::DELIVERY => 'heroicon-o-truck',
            self::PICKUP => 'heroicon-o-location-marker',
            self::HUB => 'heroicon-s-office-building',
            self::SERVICE_POINT => 'heroicon-o-shield-check',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::HOME => 'HOME',
            self::WORK => 'WORK',
            self::OTHER => 'OTHER',
            self::DELIVERY => 'DELIVERY',
            self::PICKUP => 'PICKUP',
            self::HUB => 'HUB',
            self::SERVICE_POINT => 'SERVICE POINT',
        };
    }

    public static function residentialCases(): array
    {
        return [
            self::HOME,
            self::WORK,
            self::OTHER,
        ];
    }

    public static function logisticsCases(): array
    {
        return [
            self::DELIVERY,
            self::PICKUP,
            self::HUB,
            self::SERVICE_POINT,
        ];
    }
}
