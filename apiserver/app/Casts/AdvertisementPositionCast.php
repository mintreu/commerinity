<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Advertisement Position Type Enum
 *
 * Defines the render position/shape inside a placement.
 */
enum AdvertisementPositionCast: string implements HasColor, HasIcon, HasLabel
{
    case HERO = 'hero';
    case TOP_BANNER = 'top_banner';
    case MIDDLE_BANNER = 'middle_banner';
    case BOTTOM_BANNER = 'bottom_banner';
    case SIDEBAR = 'sidebar';
    case INLINE = 'inline';
    case GRID_SLOT = 'grid_slot';
    case POPUP = 'popup';
    case STICKY = 'sticky';
    case FLOATING = 'floating';
    case BACKGROUND = 'background';

    public function getLabel(): string
    {
        return match ($this) {
            self::HERO => 'Hero',
            self::TOP_BANNER => 'Top Banner',
            self::MIDDLE_BANNER => 'Middle Banner',
            self::BOTTOM_BANNER => 'Bottom Banner',
            self::SIDEBAR => 'Sidebar',
            self::INLINE => 'Inline Content',
            self::GRID_SLOT => 'Grid Slot',
            self::POPUP => 'Popup Modal',
            self::STICKY => 'Sticky',
            self::FLOATING => 'Floating',
            self::BACKGROUND => 'Background',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HERO, self::TOP_BANNER, self::MIDDLE_BANNER, self::BOTTOM_BANNER => 'primary',
            self::SIDEBAR, self::INLINE, self::GRID_SLOT => 'success',
            self::POPUP, self::STICKY, self::FLOATING => 'warning',
            self::BACKGROUND => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HERO => 'heroicon-o-star',
            self::TOP_BANNER => 'heroicon-o-arrow-up',
            self::MIDDLE_BANNER => 'heroicon-o-minus',
            self::BOTTOM_BANNER => 'heroicon-o-arrow-down',
            self::SIDEBAR => 'heroicon-o-view-columns',
            self::INLINE => 'heroicon-o-document-text',
            self::GRID_SLOT => 'heroicon-o-squares-2x2',
            self::POPUP => 'heroicon-o-window',
            self::STICKY => 'heroicon-o-paper-clip',
            self::FLOATING => 'heroicon-o-cloud',
            self::BACKGROUND => 'heroicon-o-photo',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::HERO => 'Full-width visual hero slot',
            self::TOP_BANNER => 'Banner at top section of the area',
            self::MIDDLE_BANNER => 'Banner inserted in middle of content',
            self::BOTTOM_BANNER => 'Banner near bottom of the area',
            self::SIDEBAR => 'Narrow column ad in sidebar',
            self::INLINE => 'Embedded inside text/content flow',
            self::GRID_SLOT => 'Inserted between grid/list cards',
            self::POPUP => 'Modal overlay placement',
            self::STICKY => 'Sticky element fixed to viewport edge',
            self::FLOATING => 'Floating widget style placement',
            self::BACKGROUND => 'Background layer ad treatment',
        };
    }
}

