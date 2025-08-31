<?php

namespace Mintreu\LaravelIntegration\Facades;

use Illuminate\Support\Facades\Facade;
use Mintreu\LaravelIntegration\Contracts\PaymentIntegrationContract;
use Mintreu\LaravelIntegration\Contracts\PayoutProviderIntegrationContract;
use Mintreu\LaravelIntegration\Contracts\ShippingProviderIntegrationContract;
use Mintreu\LaravelIntegration\Contracts\SmsProviderIntegrationContract;


/**
 * @method static PaymentIntegrationContract payment(?string $provider = null)
 * @method static PayoutProviderIntegrationContract payout(?string $provider = null)
 * @method static SmsProviderIntegrationContract sms(?string $provider = null)
 * @method static ShippingProviderIntegrationContract shipping(?string $provider = null)
 *
 * @see \Mintreu\LaravelIntegration\LaravelIntegration
 */
class LaravelIntegration extends Facade
{
    protected static function getFacadeAccessor()
    {

        return \Mintreu\LaravelIntegration\LaravelIntegration::class;
    }
}
