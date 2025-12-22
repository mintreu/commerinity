<?php

namespace Mintreu\LaravelTransaction\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BeneficiaryAccountStatusCast: string implements HasColor, HasIcon, HasLabel
{
    /**
     * Account has been created but is still waiting
     * for provider / system verification before use.
     */
    case PENDING = 'pending';

    /**
     * Account has been successfully verified
     * and is ready for transactions/payouts.
     */
    case VERIFIED = 'verified';

    /**
     * Account verification failed or provider
     * explicitly rejected it (invalid details, etc.).
     */
    case REJECTED = 'rejected';

    /**
     * Account has been temporarily disabled
     * by system, provider, or admin action.
     */
    case SUSPENDED = 'suspended';

    /**
     * Account is no longer active, either because
     * the user removed it or the system deactivated it.
     */
    case INACTIVE = 'inactive';

    /**
     * Human friendly label
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING   => 'Pending Verification',
            self::VERIFIED  => 'Verified',
            self::REJECTED  => 'Rejected',
            self::SUSPENDED => 'Suspended',
            self::INACTIVE  => 'Inactive',
        };
    }

    /**
     * Heroicon (Blade UI Kit)
     */
    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING   => 'heroicon-o-clock',
            self::VERIFIED  => 'heroicon-o-check-badge',
            self::REJECTED  => 'heroicon-o-x-circle',
            self::SUSPENDED => 'heroicon-o-pause-circle',
            self::INACTIVE  => 'heroicon-o-minus-circle',
        };
    }

    /**
     * Filament color mapping
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING   => 'warning',
            self::VERIFIED  => 'success',
            self::REJECTED  => 'danger',
            self::SUSPENDED => 'info',
            self::INACTIVE  => 'gray',
        };
    }
}
