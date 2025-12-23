<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum HelpdeskStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case Open = 'open';
    case AwaitingReply = 'awaiting_reply';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => __('helpdesk.status.open'),
            self::AwaitingReply => __('helpdesk.status.awaiting_reply'),
            self::InProgress => __('helpdesk.status.in_progress'),
            self::Resolved => __('helpdesk.status.resolved'),
            self::Closed => __('helpdesk.status.closed'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'info',
            self::AwaitingReply => 'warning',
            self::InProgress => 'primary',
            self::Resolved => 'success',
            self::Closed => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Open => 'heroicon-o-inbox',
            self::AwaitingReply => 'heroicon-o-clock',
            self::InProgress => 'heroicon-o-arrow-path',
            self::Resolved => 'heroicon-o-check-circle',
            self::Closed => 'heroicon-o-lock-closed',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Open, self::AwaitingReply, self::InProgress], true);
    }

    public function canReply(): bool
    {
        return in_array($this, [self::Open, self::AwaitingReply, self::InProgress], true);
    }

    public function canClose(): bool
    {
        return in_array($this, [self::Open, self::AwaitingReply, self::InProgress, self::Resolved], true);
    }

    public function canReopen(): bool
    {
        return in_array($this, [self::Resolved, self::Closed], true);
    }
}
