<?php

namespace Mintreu\LaravelIntegration;


use BadMethodCallException;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Mintreu\LaravelIntegration\Contracts\PaymentIntegrationContract;
use Mintreu\LaravelIntegration\Contracts\PayoutProviderIntegrationContract;
use Mintreu\LaravelIntegration\Contracts\ProviderIntegrationContract;
use Mintreu\LaravelIntegration\Contracts\ShippingProviderIntegrationContract;
use Mintreu\LaravelIntegration\Contracts\SmsProviderIntegrationContract;
use Mintreu\LaravelIntegration\Models\Integration;
use Mintreu\LaravelIntegration\Support\LaravelIntegrationRegistry;
use Mintreu\Toolkit\Support\Validator\ClassContractValidator;


///**
// * @method static PaymentIntegrationContract payment(?string $provider = null)
// * @method static PayoutProviderIntegrationContract payout(?string $provider = null)
// * @method static SmsProviderIntegrationContract sms(?string $provider = null)
// * @method static ShippingProviderIntegrationContract shipping(?string $provider = null)
// */

class LaravelIntegration {

    protected array $providersByType = [];

    public function __construct()
    {
        $this->resolveProviders();
    }


    public static function make():static
    {
        return new static();
    }



    public static function __callStatic($name, $arguments)
    {
        // Allow static calls like LaravelIntegration::payment('razorpay')
        $instance = new static();
        return $instance->getProviderInstance($name, $arguments[0] ?? null);
    }

    private function resolveProviders(): void
    {
        $allProviders    = LaravelIntegrationRegistry::make()->getAllProviders();
        $activeProviders = LaravelIntegrationRegistry::getActiveProviders();


        foreach ($allProviders as $key => $config) {
            $parts = explode('-', $key);
            $type  = end($parts); // payment, payout, sms, etc.

            if (in_array($key, $activeProviders)) {
                $instance = app()->bound($key)
                    ? app($key)
                    : new $config['provider']($config['key'], $config['secret']);

                ClassContractValidator::throwUnless($instance, ProviderIntegrationContract::class);

                $interface = match ($type) {
                    IntegrationTypeCast::PAYMENT->value  => PaymentIntegrationContract::class,
                    IntegrationTypeCast::PAYOUT->value   => PayoutProviderIntegrationContract::class,
                    IntegrationTypeCast::SMS->value      => SmsProviderIntegrationContract::class,
                    IntegrationTypeCast::SHIPPING->value => ShippingProviderIntegrationContract::class,
                    default                              => ProviderIntegrationContract::class,
                };

                ClassContractValidator::throwUnless($instance, $interface);

                $this->providersByType[$type][$key] = $instance;
            }
        }
    }



    public function getProviderInstance(string $type, ?string $provider = null)
    {
        $type = strtolower($type);

        if (!isset($this->providersByType[$type])) {
            $message = app()->environment('production')
                ? "No provider available for [{$type}] service."
                : "❌ No providers registered for service type [{$type}]. 👉 Did you register any {$type} provider in LaravelIntegrationRegistry::make()? Check config/laravel-integration.php or ensure the provider is active in DB.";

            throw new BadMethodCallException($message);
        }

        if ($provider) {
            if (!isset($this->providersByType[$type][$provider])) {
                $available = implode(', ', array_keys($this->providersByType[$type]));
                $message = app()->environment('production')
                    ? "Provider [{$provider}] not found for [{$type}]."
                    : "❌ Provider [{$provider}] not found for service type [{$type}]. 👉 Available providers: {$available}. Double-check the slug or register this provider in LaravelIntegrationRegistry.";

                throw new BadMethodCallException($message);
            }

            return $this->providersByType[$type][$provider];
        }

        $default = Integration::where('type', $type)
            ->where('status', true)
            ->where('default', true)
            ->first();

        if (!$default) {
            $available = implode(', ', array_keys($this->providersByType[$type]));
            $message = app()->environment('production')
                ? "No default provider set for [{$type}]."
                : "❌ No default provider configured for service type [{$type}]. 👉 Please mark one provider as default in the `integrations` table. Currently available: {$available}.";

            throw new BadMethodCallException($message);
        }

        return $this->providersByType[$type][$default->url];
    }



}
