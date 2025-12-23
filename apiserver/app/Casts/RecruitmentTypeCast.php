<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RecruitmentTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contractual = 'contractual';
    case Internship = 'internship';

    public function getLabel(): string
    {
        return match ($this) {
            self::FullTime => __('recruitment.types.full_time'),
            self::PartTime => __('recruitment.types.part_time'),
            self::Contractual => __('recruitment.types.contractual'),
            self::Internship => __('recruitment.types.internship'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::FullTime => 'success',
            self::PartTime => 'warning',
            self::Contractual => 'gray',
            self::Internship => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::FullTime => 'heroicon-o-briefcase',
            self::PartTime => 'heroicon-o-clock',
            self::Contractual => 'heroicon-o-document-text',
            self::Internship => 'heroicon-o-academic-cap',
        };
    }
}
