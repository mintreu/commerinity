<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MessageTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case FILE = 'file';
    case SYSTEM = 'system';

    public function getLabel(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::IMAGE => 'Image',
            self::FILE => 'File',
            self::SYSTEM => 'System',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TEXT => 'gray',
            self::IMAGE => 'info',
            self::FILE => 'warning',
            self::SYSTEM => 'primary',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::TEXT => 'heroicon-o-chat-bubble-left',
            self::IMAGE => 'heroicon-o-photo',
            self::FILE => 'heroicon-o-document',
            self::SYSTEM => 'heroicon-o-cog',
        };
    }

    /**
     * Get the default message type.
     */
    public static function getDefault(): self
    {
        return self::TEXT;
    }
}
