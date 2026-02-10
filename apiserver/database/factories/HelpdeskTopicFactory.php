<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Support\HelpdeskTopic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Support\HelpdeskTopic>
 */
class HelpdeskTopicFactory extends Factory
{
    protected $model = HelpdeskTopic::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Account & Profile',
            'Payments & Billing',
            'Technical Issues',
            'Membership & Subscription',
            'Security & Privacy',
            'Mobile App',
            'Wallet & Transactions',
            'Commission & Earnings',
            'Team & Referrals',
            'General Inquiry',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement([
                'heroicon-o-user-circle',
                'heroicon-o-credit-card',
                'heroicon-o-cog',
                'heroicon-o-star',
                'heroicon-o-shield-check',
                'heroicon-o-device-phone-mobile',
                'heroicon-o-wallet',
                'heroicon-o-currency-rupee',
                'heroicon-o-user-group',
                'heroicon-o-question-mark-circle',
            ]),
            'tickable' => true,
            'active' => true,
            'order' => fake()->numberBetween(0, 10),
        ];
    }

    public function ticketable(): static
    {
        return $this->state(fn (array $attributes) => [
            'tickable' => true,
        ]);
    }

    public function faqOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'tickable' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}

