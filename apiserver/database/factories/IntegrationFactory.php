<?php

namespace Database\Factories;

use App\Casts\IntegrationTypeCast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Integration>
 */
class IntegrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Test Payment Gateway',
            'slug' => 'test-payment',
            'type' => IntegrationTypeCast::PAYMENT->value,
            'credentials' => [
                'client_id' => 'test_client_id',
                'client_secret' => 'test_client_secret',
            ],
            'settings' => [],
            'is_sandbox' => true,
            'is_active' => true,
            'is_default' => false,
        ];
    }

    /**
     * Cashfree Payment Gateway state
     */
    public function cashfree(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Cashfree Payment Gateway',
            'slug' => 'cashfree',
            'type' => IntegrationTypeCast::PAYMENT->value,
            'credentials' => [
                'app_id' => env('CASHFREE_PG_APP_ID', 'TEST123456789'),
                'secret_key' => env('CASHFREE_PG_APP_SECRET', 'test_secret_key_123'),
            ],
            'settings' => [
                'webhook_secret' => 'test_webhook_secret',
            ],
            'is_sandbox' => true,
            'is_active' => true,
            'is_default' => true,
        ]);
    }

    /**
     * Razorpay Payment Gateway state
     */
    public function razorpay(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Razorpay Payment Gateway',
            'slug' => 'razorpay-payment',
            'type' => IntegrationTypeCast::PAYMENT->value,
            'credentials' => [
                'key_id' => env('RAZORPAY_KEY', 'rzp_test_123456'),
                'key_secret' => env('RAZORPAY_SECRET', 'test_secret_123'),
            ],
            'settings' => [
                'webhook_secret' => 'test_webhook_secret',
            ],
            'is_sandbox' => true,
            'is_active' => true,
            'is_default' => false,
        ]);
    }

    /**
     * Cashfree Payout Gateway state
     */
    public function cashfreePayout(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Cashfree Payouts',
            'slug' => 'cashfree',
            'type' => IntegrationTypeCast::PAYOUT->value,
            'credentials' => [
                'app_id' => env('CASH_FREE_PAYOUT_KEY', 'CF10767277D5AKH31POKAS73D4OCKG'),
                'secret_key' => env('CASH_FREE_PAYOUT_SECRET', 'cfsk_ma_test_13060be682e594ea6d224074174c2222_fea77a9e'),
            ],
            'settings' => [],
            'is_sandbox' => true,
            'is_active' => true,
            'is_default' => true,
        ]);
    }
}
