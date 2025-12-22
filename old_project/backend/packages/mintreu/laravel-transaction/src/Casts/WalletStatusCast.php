<?php

namespace Mintreu\LaravelTransaction\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum WalletStatusCast: string implements HasLabel, HasIcon, HasColor
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case FAILED = 'failed';
    case DEACTIVE = 'deactive';

    /**
     * @return string|array|null
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::SUSPENDED => 'danger',
            self::FAILED => 'warning',
            self::DEACTIVE => 'secondary',
        };
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'heroicon-o-check-circle',
            self::SUSPENDED, self::FAILED => 'heroicon-o-x-circle',
            self::DEACTIVE => 'heroicon-o-minus-circle',
        };
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::FAILED => 'Failed',
            self::DEactive => 'Deactive',
        };
    }
}
