<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Casts\AdPlacementCast;
use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Advertisement API Controller
 *
 * Provides endpoints for fetching ads to display on frontend.
 * Supports various ad types: Native, Google, Facebook, Amazon, Custom HTML, Affiliate
 */
final class AdvertisementController extends Controller
{
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Get ads for a specific placement
     *
     * @param  string  $placement  The placement identifier (from AdPlacementCast)
     */
    public function forPlacement(Request $request, string $placement): JsonResponse
    {
        $user = $request->user();
        $cacheKey = $this->getCacheKey($placement, $user);

        $ads = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($placement, $user) {
            $query = Advertisement::query()
                ->active()
                ->scheduled()
                ->forPlacement($placement)
                ->ordered();

            // Filter by user type
            if ($user) {
                $query->forMembers();
            } else {
                $query->forGuests();
            }

            return $query->get();
        });

        // Record impressions (outside cache)
        foreach ($ads as $ad) {
            $ad->recordImpression();
        }

        return response()->json([
            'success' => true,
            'data' => $ads->map(fn ($ad) => $this->formatAd($ad)),
        ]);
    }

    /**
     * Get ads for a specific block within a placement
     */
    public function forBlock(Request $request, string $placement, string $block): JsonResponse
    {
        $user = $request->user();
        $cacheKey = $this->getCacheKey("{$placement}_{$block}", $user);

        $ads = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($placement, $block, $user) {
            $query = Advertisement::query()
                ->active()
                ->scheduled()
                ->forPlacement($placement)
                ->forBlock($block)
                ->ordered();

            if ($user) {
                $query->forMembers();
            } else {
                $query->forGuests();
            }

            return $query->get();
        });

        foreach ($ads as $ad) {
            $ad->recordImpression();
        }

        return response()->json([
            'success' => true,
            'data' => $ads->map(fn ($ad) => $this->formatAd($ad)),
        ]);
    }

    /**
     * Get multiple placements at once (for page load optimization)
     */
    public function forPage(Request $request): JsonResponse
    {
        $placements = $request->input('placements', []);
        $user = $request->user();

        if (empty($placements) || ! is_array($placements)) {
            return response()->json([
                'success' => false,
                'message' => 'Placements array is required',
            ], 422);
        }

        $result = [];

        foreach ($placements as $placement) {
            $cacheKey = $this->getCacheKey($placement, $user);

            $ads = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($placement, $user) {
                $query = Advertisement::query()
                    ->active()
                    ->scheduled()
                    ->forPlacement($placement)
                    ->ordered();

                if ($user) {
                    $query->forMembers();
                } else {
                    $query->forGuests();
                }

                return $query->get();
            });

            // Record impressions
            foreach ($ads as $ad) {
                $ad->recordImpression();
            }

            $result[$placement] = $ads->map(fn ($ad) => $this->formatAd($ad));
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Record ad click
     */
    public function recordClick(Advertisement $advertisement): JsonResponse
    {
        $advertisement->recordClick();

        return response()->json([
            'success' => true,
            'message' => 'Click recorded',
            'data' => [
                'redirect_url' => $advertisement->link_url,
                'open_in_new_tab' => $advertisement->open_in_new_tab,
            ],
        ]);
    }

    /**
     * Get available placements (for admin/frontend reference)
     */
    public function placements(): JsonResponse
    {
        $placements = collect(AdPlacementCast::cases())->map(fn ($p) => [
            'value' => $p->value,
            'label' => $p->getLabel(),
            'icon' => $p->getIcon(),
            'color' => $p->getColor(),
            'size' => $p->getSize(),
            'description' => $p->getDescription(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $placements,
        ]);
    }

    // ========================================
    // Private Methods
    // ========================================

    private function getCacheKey(string $placement, ?User $user): string
    {
        $userType = $user ? 'member' : 'guest';

        return "ads_{$placement}_{$userType}";
    }

    private function formatAd(Advertisement $ad): array
    {
        $data = [
            'id' => $ad->id,
            'slug' => $ad->slug,
            'type' => $ad->type->value,
            'type_label' => $ad->type->getLabel(),
            'placement' => $ad->placement->value,
            'placement_label' => $ad->placement->getLabel(),
            'block' => $ad->block,
            'is_premium' => $ad->is_premium,
            'position' => $ad->position,
        ];

        // Add type-specific data
        if ($ad->isNativeAd()) {
            $data = array_merge($data, [
                'title' => $ad->title,
                'description' => $ad->description,
                'link_url' => $ad->link_url,
                'link_text' => $ad->link_text ?? 'Learn More',
                'open_in_new_tab' => $ad->open_in_new_tab,
                'image' => $ad->getImageUrl(),
                'image_mobile' => $ad->getMobileImageUrl(),
            ]);
        }

        if ($ad->isThirdPartyAd()) {
            $data = array_merge($data, [
                'ad_code' => $ad->ad_code,
                'ad_unit_id' => $ad->ad_unit_id,
            ]);
        }

        if ($ad->type === \App\Casts\AdTypeCast::AFFILIATE) {
            $data = array_merge($data, [
                'affiliate_network' => $ad->affiliate_network,
                'link_url' => $ad->link_url,
                'title' => $ad->title,
                'description' => $ad->description,
                'image' => $ad->getImageUrl(),
            ]);
        }

        // Size info
        $size = $ad->getRecommendedSize();
        $data['width'] = $size['width'];
        $data['height'] = $size['height'];
        $data['is_responsive'] = $ad->is_responsive;

        return $data;
    }
}
