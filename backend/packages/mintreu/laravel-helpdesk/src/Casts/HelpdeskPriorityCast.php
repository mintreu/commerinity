<?php

namespace Mintreu\LaravelHelpdesk\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum HelpdeskPriorityCast: string implements HasColor, HasIcon, HasLabel
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function getColor(): string|array|null
    {
        return match($this) {
            self::LOW => 'green',
            self::MEDIUM => 'yellow',
            self::HIGH => 'orange',
            self::URGENT => 'red',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::LOW => 'mdi:arrow-down-bold',
            self::MEDIUM => 'mdi:arrow-right-bold',
            self::HIGH => 'mdi:arrow-up-bold',
            self::URGENT => 'mdi:alert-circle-outline',
        };
    }

    public function getLabel(): ?string
    {
        return match($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
        };
    }
}
