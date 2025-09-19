<?php

namespace Mintreu\LaravelNaukriManager\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NaukriEmploymentTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case FULL_TIME = 'full_time';
    case PART_TIME = 'part_time';
    case CONTRACTUAL = 'contractual';
    case INTERNSHIP = 'internship';

    public function getColor(): string
    {
        return match ($this) {
            self::FULL_TIME => 'success',     // green
            self::PART_TIME => 'warning',     // yellow
            self::CONTRACTUAL => 'gray',      // neutral/gray
            self::INTERNSHIP => 'info',       // blue
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::FULL_TIME => 'heroicon-o-briefcase',
            self::PART_TIME => 'heroicon-o-clock',
            self::CONTRACTUAL => 'heroicon-o-document-text',
            self::INTERNSHIP => 'heroicon-o-academic-cap',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FULL_TIME => 'Full Time',
            self::PART_TIME => 'Part Time',
            self::CONTRACTUAL => 'Contractual',
            self::INTERNSHIP => 'Internship',
        };
    }
}
