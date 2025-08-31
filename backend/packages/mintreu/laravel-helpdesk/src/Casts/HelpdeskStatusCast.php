<?php

namespace Mintreu\LaravelHelpdesk\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum HelpdeskStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case OPEN = 'open';
    case PENDING = 'pending';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function getColor(): string|array|null
    {
        return match($this) {
            self::OPEN => 'blue',
            self::PENDING => 'yellow',
            self::RESOLVED => 'green',
            self::CLOSED => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::OPEN => 'mdi:email-outline',
            self::PENDING => 'mdi:progress-clock',
            self::RESOLVED => 'mdi:check-circle-outline',
            self::CLOSED => 'mdi:close-circle-outline',
        };
    }

    public function getLabel(): ?string
    {
        return match($this) {
            self::OPEN => 'Open',
            self::PENDING => 'Pending',
            self::RESOLVED => 'Resolved',
            self::CLOSED => 'Closed',
        };
    }
}
