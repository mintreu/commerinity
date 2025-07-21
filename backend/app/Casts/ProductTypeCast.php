<?php

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Facades\Storage;

enum ProductTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case SIMPLE = 'simple';
    case CONFIGURABLE = 'configurable';
    case COMBO = 'combo';
    case BULK = 'bulk';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SIMPLE => 'Simple',
            self::CONFIGURABLE => 'Configurable',
            self::COMBO => 'Combo',
            self::BULK => 'Bulk',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::SIMPLE => 'info',
            self::CONFIGURABLE => 'warning',
            self::COMBO => 'purple',
            self::BULK => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SIMPLE => 'heroicon-m-cube',
            self::CONFIGURABLE => 'heroicon-m-adjustments-horizontal',
            self::COMBO => 'heroicon-m-bolt',
            self::BULK => 'heroicon-m-truck',
        };
    }

//    public function getMedia(): string|array|null
//    {
//        $mediaPath = storage_path('app/private/media/');
//
//        return match ($this) {
//            self::SIMPLE => $mediaPath.'type_'.self::SIMPLE->value.'jpeg',
//            self::CONFIGURABLE =>  $mediaPath.'type_'.self::CONFIGURABLE->value.'jpeg',
//            self::COMBO =>  $mediaPath.'type_'.self::COMBO->value.'jpeg',
//            self::BULK =>  $mediaPath.'type_'.self::BULK->value.'jpeg',
//        };
//    }

    public function getMedia(): string|array|null
    {
        return match ($this) {
            self::SIMPLE => asset('media/type_simple.jpeg'),
            self::CONFIGURABLE => asset('media/type_configurable.jpeg'),
            self::COMBO => asset('media/type_combo.jpeg'),
            self::BULK => asset('media/type_bulk.jpeg'),
        };
    }


}
