<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Advertisement Placement Enum
 *
 * Defines where the advertisement can be displayed on the site
 */
enum AdPlacementCast: string implements HasColor, HasIcon, HasLabel
{
    // Homepage Placements
    case HOME_HERO_BANNER = 'home_hero_banner';           // Large hero banner on homepage
    case HOME_BELOW_CATEGORIES = 'home_below_categories'; // Between category sections
    case HOME_SIDEBAR = 'home_sidebar';                   // Homepage sidebar (if any)

    // Shop/Product Placements
    case SHOP_TOP_BANNER = 'shop_top_banner';             // Banner at top of shop page
    case SHOP_SIDEBAR = 'shop_sidebar';                   // Shop page sidebar
    case PRODUCT_BELOW_GALLERY = 'product_below_gallery'; // Below product images
    case PRODUCT_SIDEBAR = 'product_sidebar';             // Product detail sidebar
    case CATEGORY_BANNER = 'category_banner';             // Category page banner

    // Cart/Checkout Placements
    case CART_SIDEBAR = 'cart_sidebar';                   // Cart page sidebar
    case CHECKOUT_BANNER = 'checkout_banner';             // Checkout page banner

    // Global Placements
    case HEADER_STRIP = 'header_strip';                   // Thin strip above header
    case FOOTER_BANNER = 'footer_banner';                 // Banner above footer
    case POPUP_MODAL = 'popup_modal';                     // Popup/modal ad
    case STICKY_BOTTOM = 'sticky_bottom';                 // Sticky bar at bottom
    case INLINE_CONTENT = 'inline_content';               // Within content areas

    // Dashboard Placements (for members)
    case DASHBOARD_SIDEBAR = 'dashboard_sidebar';         // Member dashboard sidebar
    case DASHBOARD_BANNER = 'dashboard_banner';           // Member dashboard banner

    public function getLabel(): string
    {
        return match ($this) {
            self::HOME_HERO_BANNER => 'Homepage Hero Banner',
            self::HOME_BELOW_CATEGORIES => 'Homepage Below Categories',
            self::HOME_SIDEBAR => 'Homepage Sidebar',
            self::SHOP_TOP_BANNER => 'Shop Page Top Banner',
            self::SHOP_SIDEBAR => 'Shop Page Sidebar',
            self::PRODUCT_BELOW_GALLERY => 'Product Below Gallery',
            self::PRODUCT_SIDEBAR => 'Product Page Sidebar',
            self::CATEGORY_BANNER => 'Category Page Banner',
            self::CART_SIDEBAR => 'Cart Page Sidebar',
            self::CHECKOUT_BANNER => 'Checkout Banner',
            self::HEADER_STRIP => 'Header Strip',
            self::FOOTER_BANNER => 'Footer Banner',
            self::POPUP_MODAL => 'Popup Modal',
            self::STICKY_BOTTOM => 'Sticky Bottom Bar',
            self::INLINE_CONTENT => 'Inline Content',
            self::DASHBOARD_SIDEBAR => 'Dashboard Sidebar',
            self::DASHBOARD_BANNER => 'Dashboard Banner',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HOME_HERO_BANNER, self::HOME_BELOW_CATEGORIES, self::HOME_SIDEBAR => 'primary',
            self::SHOP_TOP_BANNER, self::SHOP_SIDEBAR, self::CATEGORY_BANNER => 'success',
            self::PRODUCT_BELOW_GALLERY, self::PRODUCT_SIDEBAR => 'info',
            self::CART_SIDEBAR, self::CHECKOUT_BANNER => 'warning',
            self::HEADER_STRIP, self::FOOTER_BANNER => 'gray',
            self::POPUP_MODAL, self::STICKY_BOTTOM => 'danger',
            self::INLINE_CONTENT => 'purple',
            self::DASHBOARD_SIDEBAR, self::DASHBOARD_BANNER => 'cyan',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::HOME_HERO_BANNER => 'heroicon-o-home',
            self::HOME_BELOW_CATEGORIES => 'heroicon-o-squares-2x2',
            self::HOME_SIDEBAR => 'heroicon-o-view-columns',
            self::SHOP_TOP_BANNER => 'heroicon-o-shopping-bag',
            self::SHOP_SIDEBAR => 'heroicon-o-bars-3-center-left',
            self::PRODUCT_BELOW_GALLERY => 'heroicon-o-photo',
            self::PRODUCT_SIDEBAR => 'heroicon-o-rectangle-stack',
            self::CATEGORY_BANNER => 'heroicon-o-folder',
            self::CART_SIDEBAR => 'heroicon-o-shopping-cart',
            self::CHECKOUT_BANNER => 'heroicon-o-credit-card',
            self::HEADER_STRIP => 'heroicon-o-minus',
            self::FOOTER_BANNER => 'heroicon-o-document',
            self::POPUP_MODAL => 'heroicon-o-window',
            self::STICKY_BOTTOM => 'heroicon-o-arrow-down-on-square',
            self::INLINE_CONTENT => 'heroicon-o-document-text',
            self::DASHBOARD_SIDEBAR => 'heroicon-o-chart-bar',
            self::DASHBOARD_BANNER => 'heroicon-o-presentation-chart-line',
        };
    }

    public function getSize(): array
    {
        return match ($this) {
            self::HOME_HERO_BANNER => ['width' => 1920, 'height' => 600],
            self::HOME_BELOW_CATEGORIES => ['width' => 1200, 'height' => 250],
            self::HOME_SIDEBAR => ['width' => 300, 'height' => 250],
            self::SHOP_TOP_BANNER => ['width' => 1200, 'height' => 200],
            self::SHOP_SIDEBAR => ['width' => 300, 'height' => 600],
            self::PRODUCT_BELOW_GALLERY => ['width' => 728, 'height' => 90],
            self::PRODUCT_SIDEBAR => ['width' => 300, 'height' => 250],
            self::CATEGORY_BANNER => ['width' => 1200, 'height' => 300],
            self::CART_SIDEBAR => ['width' => 300, 'height' => 250],
            self::CHECKOUT_BANNER => ['width' => 728, 'height' => 90],
            self::HEADER_STRIP => ['width' => 1920, 'height' => 50],
            self::FOOTER_BANNER => ['width' => 1200, 'height' => 100],
            self::POPUP_MODAL => ['width' => 600, 'height' => 400],
            self::STICKY_BOTTOM => ['width' => 728, 'height' => 90],
            self::INLINE_CONTENT => ['width' => 728, 'height' => 90],
            self::DASHBOARD_SIDEBAR => ['width' => 300, 'height' => 250],
            self::DASHBOARD_BANNER => ['width' => 728, 'height' => 90],
        };
    }

    public function getDescription(): string
    {
        $size = $this->getSize();

        return "Recommended size: {$size['width']}x{$size['height']}px";
    }
}
