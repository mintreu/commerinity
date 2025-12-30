<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Casts\AdPlacementCast;
use App\Models\Advertisement;
use Illuminate\Database\Seeder;

class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample native ads for key placements
        Advertisement::factory()
            ->native()
            ->forPlacement(AdPlacementCast::HOME_HERO_BANNER)
            ->create([
                'name' => 'Homepage Hero Banner',
                'slug' => 'home-hero-main',
                'title' => 'Mega Sale - Up to 50% Off',
                'description' => 'Shop our biggest sale of the year. Limited time offer on premium products.',
                'link_text' => 'Shop Now',
                'link_url' => '/shop/deals',
                'position' => 0,
                'is_premium' => true,
            ]);

        Advertisement::factory()
            ->native()
            ->forPlacement(AdPlacementCast::HOME_BELOW_CATEGORIES)
            ->create([
                'name' => 'Homepage Mid Banner',
                'slug' => 'home-mid-banner',
                'title' => 'New Arrivals',
                'description' => 'Check out the latest products just added to our collection.',
                'link_text' => 'Explore',
                'link_url' => '/shop/products?sort=latest',
                'position' => 0,
            ]);

        Advertisement::factory()
            ->native()
            ->forPlacement(AdPlacementCast::SHOP_TOP_BANNER)
            ->create([
                'name' => 'Shop Page Banner',
                'slug' => 'shop-top-banner',
                'title' => 'Free Shipping on Orders Above ₹499',
                'description' => 'Fast delivery to your doorstep.',
                'link_text' => 'Learn More',
                'link_url' => '/shipping',
                'position' => 0,
            ]);

        // Create Google AdSense placeholder
        Advertisement::factory()
            ->google()
            ->forPlacement(AdPlacementCast::PRODUCT_SIDEBAR)
            ->create([
                'name' => 'Product Sidebar - Google',
                'slug' => 'product-sidebar-google',
                'ad_unit_id' => 'ca-pub-XXXXXXXXXXXXXXXX',
                'position' => 0,
                'is_active' => false, // Disabled until real ad unit is configured
            ]);

        // Create affiliate ad
        Advertisement::factory()
            ->affiliate()
            ->forPlacement(AdPlacementCast::FOOTER_BANNER)
            ->create([
                'name' => 'Footer Affiliate Banner',
                'slug' => 'footer-affiliate',
                'title' => 'Partner Offers',
                'description' => 'Exclusive deals from our trusted partners.',
                'affiliate_network' => 'Amazon',
                'position' => 0,
            ]);

        // Members-only promotional ad
        Advertisement::factory()
            ->native()
            ->forPlacement(AdPlacementCast::DASHBOARD_BANNER)
            ->membersOnly()
            ->create([
                'name' => 'Member Dashboard Promo',
                'slug' => 'dashboard-member-promo',
                'title' => 'Earn More Rewards',
                'description' => 'Invite friends and earn bonus BV/PV on every referral purchase.',
                'link_text' => 'Start Referring',
                'link_url' => '/dashboard/referrals',
                'position' => 0,
            ]);

        // Guest conversion ad (show to guests only)
        Advertisement::factory()
            ->native()
            ->forPlacement(AdPlacementCast::STICKY_BOTTOM)
            ->guestsOnly()
            ->create([
                'name' => 'Guest Signup CTA',
                'slug' => 'guest-signup-cta',
                'title' => 'Join Now & Get Rewards',
                'description' => 'Sign up today and earn points on your first purchase!',
                'link_text' => 'Sign Up Free',
                'link_url' => '/register',
                'position' => 0,
            ]);

        // Create a few random ads for testing
        Advertisement::factory()
            ->count(5)
            ->native()
            ->create();
    }
}
