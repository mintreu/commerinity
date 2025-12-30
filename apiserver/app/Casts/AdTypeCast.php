<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Advertisement Type Enum
 *
 * Defines the source/provider of the advertisement
 */
enum AdTypeCast: string implements HasColor, HasIcon, HasLabel
{
    case NATIVE = 'native';           // Self-hosted image/video ads
    case GOOGLE = 'google';           // Google AdSense
    case FACEBOOK = 'facebook';       // Facebook Audience Network
    case AMAZON = 'amazon';           // Amazon Associates
    case CUSTOM_HTML = 'custom_html'; // Custom HTML/JS code
    case AFFILIATE = 'affiliate';     // Affiliate partner ads

    public function getLabel(): string
    {
        return match ($this) {
            self::NATIVE => 'Native Ad',
            self::GOOGLE => 'Google AdSense',
            self::FACEBOOK => 'Facebook Ads',
            self::AMAZON => 'Amazon Associates',
            self::CUSTOM_HTML => 'Custom HTML',
            self::AFFILIATE => 'Affiliate Ad',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NATIVE => 'primary',
            self::GOOGLE => 'success',
            self::FACEBOOK => 'info',
            self::AMAZON => 'warning',
            self::CUSTOM_HTML => 'gray',
            self::AFFILIATE => 'purple',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::NATIVE => 'heroicon-o-photo',
            self::GOOGLE => 'heroicon-o-globe-alt',
            self::FACEBOOK => 'heroicon-o-user-group',
            self::AMAZON => 'heroicon-o-shopping-cart',
            self::CUSTOM_HTML => 'heroicon-o-code-bracket',
            self::AFFILIATE => 'heroicon-o-link',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::NATIVE => 'Upload your own images/videos for ads',
            self::GOOGLE => 'Display Google AdSense advertisements',
            self::FACEBOOK => 'Facebook Audience Network ads',
            self::AMAZON => 'Amazon product affiliate ads',
            self::CUSTOM_HTML => 'Custom HTML/JavaScript ad code',
            self::AFFILIATE => 'Third-party affiliate partner ads',
        };
    }
}
