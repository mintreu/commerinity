<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affiliate\AffiliateCommission;
use App\Models\Recruitment\Recruitment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Public statistics endpoint for homepage display.
 * Returns real platform metrics without exposing sensitive data.
 */
final class PublicStatsController extends Controller
{
    /**
     * Get public platform statistics for homepage display.
     * Cached for 1 hour to reduce database load.
     */
    public function homepage(): JsonResponse
    {
        $stats = Cache::remember('public_homepage_stats', now()->addHour(), function () {
            return [
                'members' => $this->getMemberCount(),
                'careers' => $this->getTotalProductsCount(),
                'payouts' => $this->getActiveCategoriesCount(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stats,
            'cached_at' => Cache::get('public_homepage_stats_timestamp', now()->toIso8601String()),
        ]);
    }

    /**
     * Get the count of active members.
     * Only counts users with 'active' status.
     */
    private function getMemberCount(): array
    {
        $count = User::count();

        return [
            'value' => $count,
            'formatted' => $this->formatNumber($count),
            'label' => 'Total Users',
        ];
    }

    /**
     * Get count of open career positions.
     */
    private function getTotalProductsCount(): array
    {
        $count = \App\Models\Ecommerce\Product::count();

        return [
            'value' => $count,
            'formatted' => $this->formatNumber($count),
            'label' => 'Total Products',
        ];
    }

    /**
     * Get total payouts processed (in paisa, converted to rupees for display).
     */
    private function getActiveCategoriesCount(): array
    {
        $count = \App\Models\Ecommerce\Category::where('status', true)->count();

        return [
            'value' => $count,
            'formatted' => $this->formatNumber($count),
            'label' => 'Active Categories',
        ];
    }

    /**
     * Format large numbers with K, L, Cr suffixes.
     */
    private function formatNumber(int $number): string
    {
        if ($number >= 10000000) {
            return round($number / 10000000, 1).'Cr+';
        }
        if ($number >= 100000) {
            return round($number / 100000, 1).'L+';
        }
        if ($number >= 1000) {
            return round($number / 1000, 1).'K+';
        }

        return (string) $number;
    }

    /**
     * Format currency in Indian format (Lakhs, Crores).
     */
    private function formatCurrency(float $amount): string
    {
        if ($amount >= 10000000) {
            return '₹'.round($amount / 10000000, 1).'Cr+';
        }
        if ($amount >= 100000) {
            return '₹'.round($amount / 100000, 1).'L+';
        }
        if ($amount >= 1000) {
            return '₹'.round($amount / 1000, 1).'K+';
        }

        return '₹'.number_format($amount, 0);
    }
}
