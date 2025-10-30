<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mintreu\LaravelCategory\Models\Category;

class StatsController extends Controller
{
    /**
     * Homepage stats - Transparent cards with colored text/icons
     */
    public function getHomepageStats()
    {
        $stats = Cache::remember('homepage_stats', now()->addMinutes(5), function () {
            // Real count from database
            $users = (int) User::count();

            // Uptime calculation
            try {
                DB::connection()->getPdo();
                $uptime = 99.9;
            } catch (\Exception $e) {
                $uptime = 0;
            }

            // Real category and product counts
            $categories = (int) Category::count();
            $products = (int) Product::count();
            $features = $categories + min($products, 200) + 50;

            return [
                [
                    'label' => 'Happy Users',
                    'value' => max($users, 0),
                    'icon' => 'mdi:account-heart',
                    'textColor' => 'purple',
                    'iconColor' => 'purple',
                    'type' => 'number',
                    'visibility' => true,
                    'isCard' => false
                ],
                [
                    'label' => '% Uptime',
                    'value' => $uptime,
                    'icon' => 'mdi:shield-check',
                    'textColor' => 'blue',
                    'iconColor' => 'blue',
                    'type' => 'decimal',
                    'visibility' => true,
                    'isCard' => false
                ],
                [
                    'label' => '+ Features',
                    'value' => max($features, 0),
                    'icon' => 'mdi:star',
                    'textColor' => 'pink',
                    'iconColor' => 'pink',
                    'type' => 'number',
                    'visibility' => true,
                    'isCard' => false
                ],
                [
                    'label' => '/7 Support',
                    'value' => '24',
                    'icon' => 'mdi:headset',
                    'textColor' => 'purple',
                    'iconColor' => 'purple',
                    'type' => 'text',
                    'visibility' => true,
                    'isCard' => false
                ]
            ];
        });

        return ['data' => $stats];
    }

    /**
     * Store hero stats - Glassmorphism cards with white text/icons
     */
    public function getHeroStats()
    {
        // NO CACHING - Get real-time data for store hero stats
        $happyCustomers = (int) User::where('status', 'active')->count();
        $formattedCustomers = $this->formatNumber($happyCustomers);

        $totalProducts = (int) Product::where('status', 'active')->count();
        $formattedProducts = $this->formatNumber($totalProducts);

        $stats = [
            [
                'label' => 'Happy Customers',
                'value' => $formattedCustomers,
                'icon' => 'mdi:account-heart',
                'textColor' => 'white',
                'iconColor' => 'pink',
                'type' => 'text',
                'visibility' => true,
                'isCard' => true
            ],
            [
                'label' => 'Products',
                'value' => $formattedProducts,
                'icon' => 'mdi:package-variant',
                'textColor' => 'white',
                'iconColor' => 'blue',
                'type' => 'text',
                'visibility' => true,
                'isCard' => true
            ],
            [
                'label' => 'Support',
                'value' => '24/7',
                'icon' => 'mdi:headset',
                'textColor' => 'white',
                'iconColor' => 'green',
                'type' => 'text',
                'visibility' => true,
                'isCard' => true
            ],
            [
                'label' => 'Delivery',
                'value' => 'Same Day',
                'icon' => 'mdi:truck-fast',
                'textColor' => 'white',
                'iconColor' => 'yellow',
                'type' => 'text',
                'visibility' => true,
                'isCard' => true
            ]
        ];

        return ['data' => $stats];
    }

    /**
     * Format numbers with proper zero handling
     * 0 -> "0"
     * 1-999 -> "1", "500", etc
     * 1000-999999 -> "1K", "1.5K", etc
     * 1000000+ -> "1M", "2.5M", etc
     */
    private function formatNumber($number)
    {
        $number = (int) $number;

        // If zero, return "0"
        if ($number === 0) {
            return '0';
        }

        // Format numbers 1-999
        if ($number < 1000) {
            return (string) $number;
        }

        // Format numbers 1K-999K
        if ($number < 1000000) {
            return number_format($number / 1000, 1) . 'K';
        }

        // Format numbers 1M+
        return number_format($number / 1000000, 1) . 'M';
    }
}
