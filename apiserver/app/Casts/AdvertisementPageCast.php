<?php

declare(strict_types=1);

namespace App\Casts;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Advertisement Page Target Enum
 *
 * Represents Nuxt page targets for ad delivery.
 */
enum AdvertisementPageCast: string implements HasColor, HasIcon, HasLabel
{
    case ALL_PAGES = 'all_pages';
    case HOME = 'home';
    case SHOP = 'shop';
    case SHOP_PRODUCTS = 'shop_products';
    case SHOP_PRODUCT_DETAIL = 'shop_product_detail';
    case CATEGORIES = 'categories';
    case CATEGORY_DETAIL = 'category_detail';
    case CART = 'cart';
    case CHECKOUT = 'checkout';
    case DASHBOARD = 'dashboard';
    case PROFILE = 'profile';
    case WALLET = 'wallet';
    case HELPDESK = 'helpdesk';
    case AUTH = 'auth';
    case CUSTOM = 'custom';

    public function getLabel(): string
    {
        return match ($this) {
            self::ALL_PAGES => 'All Pages',
            self::HOME => 'Home',
            self::SHOP => 'Shop',
            self::SHOP_PRODUCTS => 'Shop Products',
            self::SHOP_PRODUCT_DETAIL => 'Shop Product Detail',
            self::CATEGORIES => 'Categories',
            self::CATEGORY_DETAIL => 'Category Detail',
            self::CART => 'Cart',
            self::CHECKOUT => 'Checkout',
            self::DASHBOARD => 'Dashboard',
            self::PROFILE => 'Profile',
            self::WALLET => 'Wallet',
            self::HELPDESK => 'Helpdesk',
            self::AUTH => 'Auth Pages',
            self::CUSTOM => 'Custom Path Pattern',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ALL_PAGES => 'gray',
            self::HOME, self::SHOP, self::SHOP_PRODUCTS, self::SHOP_PRODUCT_DETAIL => 'success',
            self::CATEGORIES, self::CATEGORY_DETAIL => 'info',
            self::CART, self::CHECKOUT => 'warning',
            self::DASHBOARD, self::PROFILE, self::WALLET, self::HELPDESK => 'primary',
            self::AUTH, self::CUSTOM => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ALL_PAGES => 'heroicon-o-globe-alt',
            self::HOME => 'heroicon-o-home',
            self::SHOP => 'heroicon-o-shopping-bag',
            self::SHOP_PRODUCTS => 'heroicon-o-squares-2x2',
            self::SHOP_PRODUCT_DETAIL => 'heroicon-o-cube',
            self::CATEGORIES => 'heroicon-o-queue-list',
            self::CATEGORY_DETAIL => 'heroicon-o-folder',
            self::CART => 'heroicon-o-shopping-cart',
            self::CHECKOUT => 'heroicon-o-credit-card',
            self::DASHBOARD => 'heroicon-o-chart-bar',
            self::PROFILE => 'heroicon-o-user',
            self::WALLET => 'heroicon-o-wallet',
            self::HELPDESK => 'heroicon-o-lifebuoy',
            self::AUTH => 'heroicon-o-lock-closed',
            self::CUSTOM => 'heroicon-o-code-bracket',
        };
    }
}

