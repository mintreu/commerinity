<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Casts\AdPlacementCast;
use App\Casts\AdTypeCast;
use App\Models\Advertisement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Advertisement>
 */
class AdvertisementFactory extends Factory
{
    protected $model = Advertisement::class;

    public function definition(): array
    {
        $name = fake()->words(3, true).' Ad';
        $type = fake()->randomElement(AdTypeCast::cases());
        $placement = fake()->randomElement(AdPlacementCast::cases());

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'type' => $type,
            'placement' => $placement,
            'block' => fake()->optional(0.3)->word(),
            'is_active' => true,
            'is_premium' => fake()->boolean(20),
            'starts_at' => fake()->optional(0.5)->dateTimeBetween('-1 month', 'now'),
            'ends_at' => fake()->optional(0.5)->dateTimeBetween('+1 week', '+3 months'),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(10),
            'link_url' => fake()->url(),
            'link_text' => fake()->randomElement(['Shop Now', 'Learn More', 'Get Started', 'Buy Now', 'Discover']),
            'open_in_new_tab' => true,
            'position' => fake()->numberBetween(0, 10),
            'show_to_guests' => true,
            'show_to_members' => true,
            'is_responsive' => true,
            'impressions' => fake()->numberBetween(0, 10000),
            'clicks' => fake()->numberBetween(0, 500),
        ];
    }

    /**
     * Native ad with image content
     */
    public function native(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AdTypeCast::NATIVE,
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(2),
            'link_url' => fake()->url(),
            'link_text' => fake()->randomElement(['Shop Now', 'Learn More', 'Get Offer']),
        ]);
    }

    /**
     * Google AdSense ad
     */
    public function google(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AdTypeCast::GOOGLE,
            'ad_unit_id' => 'ca-pub-'.fake()->numerify('################'),
            'ad_code' => '<ins class="adsbygoogle" data-ad-client="ca-pub-1234567890" data-ad-slot="1234567890"></ins>',
        ]);
    }

    /**
     * Custom HTML ad
     */
    public function customHtml(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AdTypeCast::CUSTOM_HTML,
            'ad_code' => '<div class="custom-ad"><a href="'.fake()->url().'"><img src="https://via.placeholder.com/728x90" alt="Ad"></a></div>',
        ]);
    }

    /**
     * Affiliate ad
     */
    public function affiliate(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AdTypeCast::AFFILIATE,
            'affiliate_network' => fake()->randomElement(['Amazon', 'Flipkart', 'Myntra', 'ShareASale']),
            'affiliate_tracking_id' => fake()->bothify('??-###-###'),
            'link_url' => fake()->url(),
            'title' => fake()->sentence(5),
            'description' => fake()->sentence(10),
        ]);
    }

    /**
     * Inactive ad
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Premium ad space
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
        ]);
    }

    /**
     * Specific placement
     */
    public function forPlacement(AdPlacementCast $placement): static
    {
        return $this->state(fn (array $attributes) => [
            'placement' => $placement,
        ]);
    }

    /**
     * Scheduled ad (starts in future)
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => fake()->dateTimeBetween('+1 day', '+1 week'),
            'ends_at' => fake()->dateTimeBetween('+2 weeks', '+2 months'),
        ]);
    }

    /**
     * Expired ad
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'ends_at' => fake()->dateTimeBetween('-1 week', '-1 day'),
        ]);
    }

    /**
     * Members only
     */
    public function membersOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_to_guests' => false,
            'show_to_members' => true,
        ]);
    }

    /**
     * Guests only
     */
    public function guestsOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_to_guests' => true,
            'show_to_members' => false,
        ]);
    }
}
