<?php

namespace Mintreu\LaravelIntegration\Facades;

use Illuminate\Support\Facades\Facade;
use Mintreu\LaravelIntegration\Contracts\PaymentIntegrationContract;
use Mintreu\LaravelIntegration\Contracts\PayoutProviderIntegrationContract;
use Mintreu\LaravelIntegration\Contracts\ShippingProviderIntegrationContract;
use Mintreu\LaravelIntegration\Contracts\SmsProviderIntegrationContract;
use Mintreu\LaravelIntegration\Contracts\BookingProviderIntegrationContract;
use Illuminate\Support\Collection;

/**
 * Laravel Integration Facade
 *
 * Provides static access to integration providers for payment, payout, SMS, shipping, and booking services.
 *
 * @method static \Mintreu\LaravelIntegration\LaravelIntegration make()
 *
 * ## Provider Instance Methods
 * @method static PaymentIntegrationContract payment(?string $provider = null)
 * @method static PayoutProviderIntegrationContract payout(?string $provider = null)
 * @method static SmsProviderIntegrationContract sms(?string $provider = null)
 * @method static ShippingProviderIntegrationContract shipping(?string $provider = null)
 * @method static BookingProviderIntegrationContract|mixed booking(?string $provider = null)
 *
 * ## Provider Retrieval Methods
 * @method static mixed getProviderInstance(string $type, ?string $provider = null) Get a specific provider instance or default provider for a type
 * @method static mixed getDefaultProvider(string $type) Get the default provider instance for a specific type
 * @method static array getAllProviders(string $type) Get all provider instances for a specific type
 *
 * ## Provider Options Methods (for FilamentPHP Select Fields)
 * @method static array getProviderOptions(string $type) Get provider options for select field in ['value' => 'label'] format
 * @method static array getPaymentOptions() Get payment provider options for select field
 * @method static array getPayoutOptions() Get payout provider options for select field
 * @method static array getSmsOptions() Get SMS provider options for select field
 * @method static array getShippingOptions() Get shipping provider options for select field
 * @method static array getBookingOptions() Get booking provider options for select field
 * @method static array getGroupedProviderOptions(?array $types = null) Get provider options grouped by type for multi-select scenarios
 *
 * ## Provider Information Methods
 * @method static array getAvailableTypes() Get all available integration types from the enum
 * @method static bool hasProvider(string $type, ?string $provider = null) Check if a provider exists for a specific type
 * @method static int getProviderCount(string $type) Get provider count for a specific type
 * @method static Collection getProvidersWithMetadata(?string $type = null) Get all active providers with metadata
 *
 * @see \Mintreu\LaravelIntegration\LaravelIntegration
 *
 * @example
 * // Get default payment provider
 * $payment = LaravelIntegration::payment();
 *
 * // Get specific payment provider
 * $razorpay = LaravelIntegration::payment('razorpay-payment');
 *
 * // Get payment options for select field
 * $options = LaravelIntegration::getPaymentOptions();
 *
 * // Get default provider instance
 * $defaultSms = LaravelIntegration::getDefaultProvider('sms');
 *
 * // Get all providers for a type
 * $allPayoutProviders = LaravelIntegration::getAllProviders('payout');
 *
 * // Check if provider exists
 * $exists = LaravelIntegration::hasProvider('payment', 'stripe-payment');
 *
 * // Get grouped options for multiple types
 * $grouped = LaravelIntegration::getGroupedProviderOptions(['payment', 'payout']);
 */
class LaravelIntegration extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Mintreu\LaravelIntegration\LaravelIntegration::class;
    }
}
