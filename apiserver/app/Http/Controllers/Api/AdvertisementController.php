<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Casts\AdPlacementCast;
use App\Casts\AdvertisementPageCast;
use App\Casts\AdvertisementPositionCast;
use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        $positionType = $this->normalizePositionType($request->input('position_type'));
        $pagePath = $this->normalizePagePath($request->input('page_path'));
        $cacheKey = $this->getCacheKey(
            $this->buildPlacementCacheSegment($placement, null, $positionType, $pagePath),
            $user
        );

        $ads = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($placement, $positionType, $pagePath, $user) {
            $query = Advertisement::query()
                ->active()
                ->scheduled()
                ->forPlacement($placement)
                ->ordered();

            if ($positionType) {
                $query->forPositionType($positionType);
            }

            $query->forPagePath($pagePath);

            // Filter by user type
            if ($user) {
                $query->forMembers();
            } else {
                $query->forGuests();
            }

            return $query->get();
        });

        // Record impressions in one write query for large tables.
        $this->recordImpressions($ads);

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
        $positionType = $this->normalizePositionType($request->input('position_type'));
        $pagePath = $this->normalizePagePath($request->input('page_path'));
        $cacheKey = $this->getCacheKey(
            $this->buildPlacementCacheSegment($placement, $block, $positionType, $pagePath),
            $user
        );

        $ads = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($placement, $block, $positionType, $pagePath, $user) {
            $query = Advertisement::query()
                ->active()
                ->scheduled()
                ->forPlacement($placement)
                ->forBlock($block)
                ->ordered();

            if ($positionType) {
                $query->forPositionType($positionType);
            }

            $query->forPagePath($pagePath);

            if ($user) {
                $query->forMembers();
            } else {
                $query->forGuests();
            }

            return $query->get();
        });

        $this->recordImpressions($ads);

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

        foreach ($placements as $placementItem) {
            $placement = null;
            $block = null;
            $positionType = null;
            $pagePath = $this->normalizePagePath($request->input('page_path'));

            if (is_string($placementItem)) {
                $placement = $placementItem;
            }

            if (is_array($placementItem)) {
                $placement = isset($placementItem['placement']) && is_string($placementItem['placement'])
                    ? $placementItem['placement']
                    : null;
                $block = isset($placementItem['block']) && is_string($placementItem['block']) && $placementItem['block'] !== ''
                    ? $placementItem['block']
                    : null;
                $positionType = $this->normalizePositionType($placementItem['position_type'] ?? null);
            }

            if (! is_string($placement) || $placement === '') {
                continue;
            }

            $cacheSegment = $this->buildPlacementCacheSegment($placement, $block, $positionType, $pagePath);
            $cacheKey = $this->getCacheKey($cacheSegment, $user);

            $ads = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($placement, $block, $positionType, $pagePath, $user) {
                $query = Advertisement::query()
                    ->active()
                    ->scheduled()
                    ->forPlacement($placement)
                    ->ordered();

                if ($block) {
                    $query->forBlock($block);
                }

                if ($positionType) {
                    $query->forPositionType($positionType);
                }

                $query->forPagePath($pagePath);

                if ($user) {
                    $query->forMembers();
                } else {
                    $query->forGuests();
                }

                return $query->get();
            });

            $this->recordImpressions($ads);

            $result[$cacheSegment] = $ads->map(fn ($ad) => $this->formatAd($ad));
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

    /**
     * Get supported position types.
     */
    public function positionTypes(): JsonResponse
    {
        $positionTypes = collect(AdvertisementPositionCast::cases())->map(fn ($p) => [
            'value' => $p->value,
            'label' => $p->getLabel(),
            'icon' => $p->getIcon(),
            'color' => $p->getColor(),
            'description' => $p->getDescription(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $positionTypes,
        ]);
    }

    /**
     * Get supported page targets.
     */
    public function pageTargets(): JsonResponse
    {
        $pageTargets = collect(AdvertisementPageCast::cases())->map(fn ($p) => [
            'value' => $p->value,
            'label' => $p->getLabel(),
            'icon' => $p->getIcon(),
            'color' => $p->getColor(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $pageTargets,
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

    private function buildPlacementCacheSegment(string $placement, ?string $block, ?string $positionType, ?string $pagePath = null): string
    {
        $segment = $placement;

        if ($block) {
            $segment .= "_block_{$block}";
        }

        if ($positionType) {
            $segment .= "_position_{$positionType}";
        }

        if ($pagePath) {
            $segment .= '_page_'.md5($pagePath);
        }

        return $segment;
    }

    private function normalizePositionType(mixed $positionType): ?string
    {
        if (! is_string($positionType) || $positionType === '') {
            return null;
        }

        return AdvertisementPositionCast::tryFrom($positionType)?->value;
    }

    private function normalizePagePath(mixed $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $normalized = '/'.ltrim(trim($path), '/');

        return $normalized === '//' ? '/' : $normalized;
    }

    private function recordImpressions(EloquentCollection $ads): void
    {
        if ($ads->isEmpty()) {
            return;
        }

        $ids = $ads->pluck('id')->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return;
        }

        $now = now();

        Advertisement::query()->whereIn('id', $ids)->update([
            'impressions' => DB::raw('impressions + 1'),
            'last_impression_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($ads as $ad) {
            $ad->impressions = (int) $ad->impressions + 1;
            $ad->last_impression_at = $now;
            $ad->updated_at = $now;
        }
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
            'page_target' => $ad->page_target?->value,
            'page_target_label' => $ad->page_target?->getLabel(),
            'page_pattern' => $ad->page_pattern,
            'block' => $ad->block,
            'is_premium' => $ad->is_premium,
            'position' => $ad->position,
            'position_type' => $ad->position_type?->value,
            'position_type_label' => $ad->position_type?->getLabel(),
            'position_config' => $ad->position_config ?? [],
            'impressions' => $ad->impressions,
            'target_views' => $ad->target_views,
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
                'third_party' => $ad->getThirdPartyPayload(),
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
