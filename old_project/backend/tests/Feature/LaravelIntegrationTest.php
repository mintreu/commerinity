<?php

use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelIntegration\Models\Integration;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mintreu\LaravelIntegration\Contracts\PaymentIntegrationContract;
use Mintreu\LaravelIntegration\Contracts\SmsProviderIntegrationContract;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Reset the LaravelIntegrationRegistry to clear any cached providers
    app()->forgetInstance(\Mintreu\LaravelIntegration\Support\LaravelIntegrationRegistry::class);

    // Clear the providers config to avoid TypeError during auto-resolution in tests where we don't need it
    config()->set('laravel-integration.providers', []);

    // Run the seeder to populate the integrations table
    $this->seed(\Database\Seeders\IntegrationSeeder::class);
});



it('can retrieve available providers by type', function () {
    // Seeder runs in beforeEach. We need to set the config to match the seeder data.
    config()->set('laravel-integration.providers', [
        'payment' => [
            'razorpay' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payment\Razorpay\RazorpayPaymentProvider::class, 'key' => '', 'secret' => ''],
            'cash-free' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payment\CashFree\CashFreePaymentProvider::class, 'key' => '', 'secret' => ''],
            'cash' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payment\Cash\CashPaymentProvider::class, 'key' => '', 'secret' => ''],
            'wallet' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payment\Wallet\WalletPaymentProvider::class, 'key' => '', 'secret' => ''],
        ]
    ]);

    app()->forgetInstance(\Mintreu\LaravelIntegration\Support\LaravelIntegrationRegistry::class);

    $integration = LaravelIntegration::make();
    $paymentProviders = $integration->getAvailableProviders(IntegrationTypeCast::PAYMENT->value);

    expect($paymentProviders)->toBeArray()
        ->and(count($paymentProviders))->toBe(4)
        ->and($paymentProviders['cash-free-payment']['default'])->toBeTrue();
});

it('can retrieve available providers without type filtering', function () {
    config()->set('laravel-integration.providers', [
        'payment' => [
            'razorpay' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payment\Razorpay\RazorpayPaymentProvider::class, 'key' => '', 'secret' => ''],
            'cash-free' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payment\CashFree\CashFreePaymentProvider::class, 'key' => '', 'secret' => ''],
            'cash' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payment\Cash\CashPaymentProvider::class, 'key' => '', 'secret' => ''],
            'wallet' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payment\Wallet\WalletPaymentProvider::class, 'key' => '', 'secret' => ''],
        ],
        'payout' => [
            'razorpay' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payout\Razorpay\RazorpayPayoutProvider::class, 'key' => '', 'secret' => ''],
            'cash-free' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payout\CashFree\CashFreePayoutProvider::class, 'key' => '', 'secret' => ''],
        ],
        'sms' => [
            'fast2sms' => ['provider' => \Mintreu\LaravelIntegration\Providers\Sms\Fast2Sms\Fast2SmsProvider::class, 'key' => '', 'secret' => ''],
        ]
    ]);

    app()->forgetInstance(\Mintreu\LaravelIntegration\Support\LaravelIntegrationRegistry::class);

    $integration = LaravelIntegration::make();
    $allProviders = $integration->getAvailableProviders();

    expect($allProviders)->toBeArray()
        ->and($allProviders)->toHaveKeys([
            IntegrationTypeCast::PAYMENT->value,
            IntegrationTypeCast::PAYOUT->value,
            IntegrationTypeCast::SMS->value
        ])
        ->and(count($allProviders[IntegrationTypeCast::PAYMENT->value]))->toBe(4)
        ->and(count($allProviders[IntegrationTypeCast::PAYOUT->value]))->toBe(2)
        ->and(count($allProviders[IntegrationTypeCast::SMS->value]))->toBe(1);
});

it('can retrieve a specific provider instance by type and slug', function () {
    $slug = 'razorpay-payment';
    $providerClass = \Mintreu\LaravelIntegration\Providers\Payment\Razorpay\RazorpayPaymentProvider::class;

    config()->set('laravel-integration.providers', [
        'payment' => [
            'razorpay' => [
                'provider' => $providerClass,
                'key' => 'test',
                'secret' => 'test'
            ],
        ],
    ]);

    app()->forgetInstance(\Mintreu\LaravelIntegration\Support\LaravelIntegrationRegistry::class);

    $paymentProvider = LaravelIntegration::payment($slug);

    expect($paymentProvider)->toBeInstanceOf(PaymentIntegrationContract::class)
        ->and($paymentProvider)->toBeInstanceOf($providerClass);
});

it('can retrieve the default provider instance by type', function () {
    $defaultProviderClass = \Mintreu\LaravelIntegration\Providers\Payment\CashFree\CashFreePaymentProvider::class;

    config()->set('laravel-integration.providers', [
        'payment' => [
            'cash-free' => ['provider' => $defaultProviderClass, 'key' => '', 'secret' => ''],
            'razorpay' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payment\Razorpay\RazorpayPaymentProvider::class, 'key' => '', 'secret' => ''],
            'cash' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payment\Cash\CashPaymentProvider::class, 'key' => '', 'secret' => ''],
            'wallet' => ['provider' => \Mintreu\LaravelIntegration\Providers\Payment\Wallet\WalletPaymentProvider::class, 'key' => '', 'secret' => ''],
        ]
    ]);

    app()->forgetInstance(\Mintreu\LaravelIntegration\Support\LaravelIntegrationRegistry::class);

    $defaultPaymentProvider = LaravelIntegration::payment();

    expect($defaultPaymentProvider)->toBeInstanceOf(PaymentIntegrationContract::class)
        ->and($defaultPaymentProvider)->toBeInstanceOf($defaultProviderClass);
});

it('throws BadMethodCallException for non-existent provider type', function () {
    expect(function () {
        LaravelIntegration::nonexistentType();
    })->toThrow(BadMethodCallException::class);
});

it('throws BadMethodCallException for non-existent specific provider', function () {
    config()->set('laravel-integration.providers', [
        'sms' => [
            'fast2sms' => ['provider' => \Mintreu\LaravelIntegration\Providers\Sms\Fast2Sms\Fast2SmsProvider::class, 'key' => '', 'secret' => ''],
        ]
    ]);

    app()->forgetInstance(\Mintreu\LaravelIntegration\Support\LaravelIntegrationRegistry::class);

    expect(function () {
        LaravelIntegration::sms('nonexistent-sms-provider');
    })->toThrow(BadMethodCallException::class);
});

it('throws BadMethodCallException if no default provider is configured for a type', function () {
    // This test needs a specific state, so we override the seeder's data.
    Integration::query()->delete();

    config()->set('laravel-integration.providers', [
        'sms' => [
            'sms-no-default' => [
                'name' => 'SMS Provider Without Default',
                'provider' => \Mintreu\LaravelIntegration\Providers\Sms\Fast2Sms\Fast2SmsProvider::class,
                'key' => 'sms-key',
                'secret' => 'sms-secret',
            ],
        ],
    ]);

    Integration::create([
        'name' => 'SMS Provider Without Default',
        'url' => 'sms-no-default-sms',
        'type' => IntegrationTypeCast::SMS->value,
        'status' => true,
        'default' => false,
        'key' => 'sms-key',
        'secret' => 'sms-secret',
    ]);

    app()->forgetInstance(\Mintreu\LaravelIntegration\Support\LaravelIntegrationRegistry::class);

    expect(function () {
        LaravelIntegration::sms();
    })->toThrow(BadMethodCallException::class);
});

// Test that the provider is actually instantiated with the correct keys and secrets
it('instantiates a real provider with correct configuration', function () {
    // This test needs a specific state, so we override the seeder's data.
    Integration::query()->delete();

    $slug = 'test-cash-payment';
    $key = 'test-key';
    $secret = 'test-secret';
    $providerClass = \Mintreu\LaravelIntegration\Providers\Payment\Cash\CashPaymentProvider::class;

    // Set the config for the provider
    config()->set('laravel-integration.providers', [
        'payment' => [
            'test-cash' => [ // This is the part of the slug before '-payment'
                'name' => 'Test Cash Payment',
                'provider' => $providerClass,
                'key' => $key,
                'secret' => $secret,
            ],
        ],
    ]);

    // Create an Integration record to make it "active"
    Integration::create([
        'name' => 'Test Cash Payment',
        'url' => 'test-cash-payment', // The full slug
        'type' => IntegrationTypeCast::PAYMENT->value,
        'status' => true,
        'default' => true,
        'key' => $key,
        'secret' => $secret,
    ]);

    // Reset registry to pick up new config/db state
    app()->forgetInstance(\Mintreu\LaravelIntegration\Support\LaravelIntegrationRegistry::class);

    $providerInstance = LaravelIntegration::payment('test-cash-payment');

    expect($providerInstance)->toBeInstanceOf($providerClass);
});

