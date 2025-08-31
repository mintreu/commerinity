<?php

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NaukriApplicationStatusCast: string implements HasColor, HasIcon, HasLabel
{

    case AWAITING_PAYMENT = 'awaiting_payment';
    case SUBMITTED        = 'submitted';
    case UNDER_REVIEW     = 'under_review';
    case ACCEPTED         = 'accepted';
    case REJECTED         = 'rejected';
    case WITHDRAWN        = 'withdrawn';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AWAITING_PAYMENT => 'Awaiting Payment',
            self::SUBMITTED        => 'Submitted',
            self::UNDER_REVIEW     => 'Under Review',
            self::ACCEPTED         => 'Accepted',
            self::REJECTED         => 'Rejected',
            self::WITHDRAWN        => 'Withdrawn',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::AWAITING_PAYMENT => 'primary',
            self::SUBMITTED        => 'secondary',
            self::UNDER_REVIEW     => 'warning',
            self::ACCEPTED         => 'success',
            self::REJECTED         => 'danger',
            self::WITHDRAWN        => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::AWAITING_PAYMENT => 'heroicon-m-clock',
            self::SUBMITTED        => 'heroicon-m-check-circle',
            self::UNDER_REVIEW     => 'heroicon-m-eye',
            self::ACCEPTED         => 'heroicon-m-check',
            self::REJECTED         => 'heroicon-m-x-circle',
            self::WITHDRAWN        => 'heroicon-m-ban',
        };
    }

}
