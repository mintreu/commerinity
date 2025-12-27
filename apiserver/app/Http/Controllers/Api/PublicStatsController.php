<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mlm\MlmCommission;
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
                'careers' => $this->getOpenPositionsCount(),
                'payouts' => $this->getTotalPayouts(),
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
        $count = User::where('status', 'active')->count();

        return [
            'value' => $count,
            'formatted' => $this->formatNumber($count),
            'label' => 'Active Members',
        ];
    }

    /**
     * Get count of open career positions.
     */
    private function getOpenPositionsCount(): array
    {
        $count = Recruitment::where('status', 'open')
            ->where(function ($query) {
                $query->whereNull('close_date')
                    ->orWhere('close_date', '>', now());
            })
            ->count();

        return [
            'value' => $count,
            'formatted' => (string) $count,
            'label' => 'Open Positions',
        ];
    }

    /**
     * Get total payouts processed (in paisa, converted to rupees for display).
     */
    private function getTotalPayouts(): array
    {
        $totalPaisa = MlmCommission::where('status', 'processed')->sum('amount');
        $totalRupees = $totalPaisa / 100;

        return [
            'value' => $totalRupees,
            'formatted' => $this->formatCurrency($totalRupees),
            'label' => 'Total Paid Out',
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
