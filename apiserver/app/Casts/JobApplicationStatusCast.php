<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum JobApplicationStatusCast: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case AwaitingPayment = 'awaiting_payment';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => __('recruitment.application_status.draft'),
            self::AwaitingPayment => __('recruitment.application_status.awaiting_payment'),
            self::Submitted => __('recruitment.application_status.submitted'),
            self::UnderReview => __('recruitment.application_status.under_review'),
            self::Accepted => __('recruitment.application_status.accepted'),
            self::Rejected => __('recruitment.application_status.rejected'),
            self::Withdrawn => __('recruitment.application_status.withdrawn'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::AwaitingPayment => 'primary',
            self::Submitted => 'info',
            self::UnderReview => 'warning',
            self::Accepted => 'success',
            self::Rejected => 'danger',
            self::Withdrawn => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-square',
            self::AwaitingPayment => 'heroicon-o-clock',
            self::Submitted => 'heroicon-o-paper-airplane',
            self::UnderReview => 'heroicon-o-eye',
            self::Accepted => 'heroicon-o-check-circle',
            self::Rejected => 'heroicon-o-x-circle',
            self::Withdrawn => 'heroicon-o-arrow-uturn-left',
        };
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::Draft, self::AwaitingPayment]);
    }

    public function canWithdraw(): bool
    {
        return in_array($this, [self::Submitted, self::UnderReview]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Accepted, self::Rejected, self::Withdrawn]);
    }
}
