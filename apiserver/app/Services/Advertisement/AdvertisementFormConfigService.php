<?php

declare(strict_types=1);

namespace App\Services\Advertisement;

use App\Casts\AdPlacementCast;
use App\Casts\AdTypeCast;
use App\Casts\AdvertisementPageCast;
use App\Casts\AdvertisementPositionCast;

final class AdvertisementFormConfigService
{
    /**
     * @return array<string, string>
     */
    public function getBlockOptions(?string $placement): array
    {
        return match ($placement) {
            AdPlacementCast::HOME_HERO_BANNER->value => [
                'primary' => 'Primary Hero',
                'secondary' => 'Secondary Hero',
            ],
            AdPlacementCast::SHOP_TOP_BANNER->value => [
                'promo' => 'Promo Banner',
                'campaign' => 'Campaign Banner',
            ],
            AdPlacementCast::SHOP_SIDEBAR->value,
            AdPlacementCast::PRODUCT_SIDEBAR->value,
            AdPlacementCast::DASHBOARD_SIDEBAR->value => [
                'left' => 'Left Rail',
                'right' => 'Right Rail',
            ],
            AdPlacementCast::INLINE_CONTENT->value => [
                'before_content' => 'Before Content',
                'mid_content' => 'Mid Content',
                'after_content' => 'After Content',
            ],
            AdPlacementCast::POPUP_MODAL->value => [
                'entry' => 'Entry Popup',
                'exit' => 'Exit Popup',
                'timed' => 'Timed Popup',
            ],
            AdPlacementCast::STICKY_BOTTOM->value => [
                'mobile' => 'Mobile Sticky',
                'desktop' => 'Desktop Sticky',
            ],
            default => [
                'default' => 'Default Block',
            ],
        };
    }

    public function getDefaultPositionTypeForPlacement(?string $placement): string
    {
        return match ($placement) {
            AdPlacementCast::HOME_HERO_BANNER->value => AdvertisementPositionCast::HERO->value,
            AdPlacementCast::SHOP_TOP_BANNER->value,
            AdPlacementCast::HEADER_STRIP->value,
            AdPlacementCast::CHECKOUT_BANNER->value,
            AdPlacementCast::DASHBOARD_BANNER->value => AdvertisementPositionCast::TOP_BANNER->value,
            AdPlacementCast::SHOP_SIDEBAR->value,
            AdPlacementCast::PRODUCT_SIDEBAR->value,
            AdPlacementCast::CART_SIDEBAR->value,
            AdPlacementCast::DASHBOARD_SIDEBAR->value => AdvertisementPositionCast::SIDEBAR->value,
            AdPlacementCast::INLINE_CONTENT->value => AdvertisementPositionCast::INLINE->value,
            AdPlacementCast::POPUP_MODAL->value => AdvertisementPositionCast::POPUP->value,
            AdPlacementCast::STICKY_BOTTOM->value => AdvertisementPositionCast::STICKY->value,
            AdPlacementCast::FOOTER_BANNER->value => AdvertisementPositionCast::BOTTOM_BANNER->value,
            default => AdvertisementPositionCast::TOP_BANNER->value,
        };
    }

    public function isNativeLikeType(AdTypeCast|string|null $type): bool
    {
        $value = $type instanceof AdTypeCast ? $type->value : $type;

        return in_array($value, [AdTypeCast::NATIVE->value, AdTypeCast::AFFILIATE->value], true);
    }

    public function isThirdPartyType(AdTypeCast|string|null $type): bool
    {
        $value = $type instanceof AdTypeCast ? $type->value : $type;

        return in_array($value, [
            AdTypeCast::GOOGLE->value,
            AdTypeCast::FACEBOOK->value,
            AdTypeCast::AMAZON->value,
            AdTypeCast::CUSTOM_HTML->value,
        ], true);
    }

    public function isAffiliateType(AdTypeCast|string|null $type): bool
    {
        $value = $type instanceof AdTypeCast ? $type->value : $type;

        return $value === AdTypeCast::AFFILIATE->value;
    }

    public function isCustomPageTarget(AdvertisementPageCast|string|null $target): bool
    {
        $value = $target instanceof AdvertisementPageCast ? $target->value : $target;

        return $value === AdvertisementPageCast::CUSTOM->value;
    }
}

