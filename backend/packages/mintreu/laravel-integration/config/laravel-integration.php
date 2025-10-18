<?php

/*
|--------------------------------------------------------------------------
| Laravel Integration Configuration
|--------------------------------------------------------------------------
|
| This file contains configuration for the Mintreu/LaravelIntegration package.
| You can define your default integrations, API credentials, webhooks, and
| provider classes for each integration type.
|
| Integration Types:
| - PAYMENT   : Online payment gateways for collecting payments.
| - PAYOUT    : Payout / disbursement gateways for transferring funds.
| - SHIPPING  : Shipping / courier providers for order fulfillment.
| - SMS       : SMS gateway providers for sending messages.
| - BOOKING   : Hotel or service booking providers.
|
| For each integration, define:
| - key     : Your API key for authentication.
| - secret  : Your API secret or token.
| - webhook : Optional callback URL for event notifications.
| - provider: Fully qualified Laravel ServiceProvider class for the integration.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Default Integration Selection
    |--------------------------------------------------------------------------
    |
    | Specify which provider should be used by default for each integration type.
    | Whenever you use the integration without explicitly selecting a provider,
    | the default one will be used.
    |
    */
    'default' => [
        'payment'   => 'razorpay',   // Default online payment provider
        'payout'    => 'razorpay',   // Default payout/disbursement provider
        'shipping'  => 'shiprocket', // Default shipping/courier provider
        'sms'       => 'fast2sms',   // Default SMS gateway
        'booking'   => 'hotel',      // Default booking provider
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Providers Configuration
    |--------------------------------------------------------------------------
    |
    | List all available providers for each integration type.
    | Each provider must include API credentials, optional webhook URLs, and
    | the Laravel ServiceProvider class.
    |
    | Adding new providers is straightforward: define a unique key and fill in
    | the credentials and provider class.
    |
    */
    'providers' => [

        /*
        |--------------------------------------------------------------------------
        | Online Payment Gateways
        |--------------------------------------------------------------------------
        |
        | These are used to collect payments from your customers.
        | Each provider here must define:
        | - key: API key to authenticate requests
        | - secret: API secret or token
        | - webhook: URL to receive events (like payment success, failure)
        | - provider: Laravel ServiceProvider for integration
        |
        */
        'payment' => [
            'cash' => [
                'key'      => null,
                'secret'   => null,
                'webhook'  => null,
                'api'      => null,
                'dev'       => false,
                'provider' => \Mintreu\LaravelIntegration\Providers\Payment\Cash\CashPaymentProvider::class,
            ],

            'wallet' => [
                'key'      => null,
                'secret'   => null,
                'webhook'  => null,
                'api'      => null,
                'dev'       => false,
                'provider' => \Mintreu\LaravelIntegration\Providers\Payment\Wallet\WalletPaymentProvider::class,
            ],


            'razorpay' => [
                'key'      => env('RAZORPAY_KEY', ''),
                'secret'   => env('RAZORPAY_SECRET', ''),
                'webhook'  => env('RAZORPAY_WEBHOOK', ''),
                'api'      => [
                  'test'    => 'https://api.razorpay.com/v1/',
                  'prod'    => 'https://api.razorpay.com/v1/'
                ],
                'dev'       => true,
                'provider' => \Mintreu\LaravelIntegration\Providers\Payment\Razorpay\RazorpayPaymentProvider::class,
            ],
            'cash-free' => [
                'key'      => env('CASH_FREE_KEY', ''),
                'secret'   => env('CASH_FREE_SECRET', ''),
                'webhook'  => env('CASH_FREE_WEBHOOK', ''),
                'api'      => [
                    'test'    => 'https://sandbox.cashfree.com/pg/',
                    'prod'    => 'https://api.cashfree.com/pg/'
                ],
                'dev'       => true,
                'provider' => \Mintreu\LaravelIntegration\Providers\Payment\CashFree\CashFreePaymentProvider::class,
            ],
            'paytm' => [
                'key'      => env('CASH_FREE_KEY', ''),
                'secret'   => env('CASH_FREE_SECRET', ''),
                'webhook'  => env('CASH_FREE_WEBHOOK', ''),
                'api'      => [
                    'test'    => 'https://sandbox.cashfree.com/pg/',
                    'prod'    => 'https://api.cashfree.com/pg/'
                ],
                'provider' => \Mintreu\LaravelIntegration\Providers\Payment\Paytm\PaytmPaymentProvider::class,
            ],





        ],

        /*
        |--------------------------------------------------------------------------
        | Payout / Disbursement Gateways
        |--------------------------------------------------------------------------
        |
        | Use these to transfer money from your system to users, vendors, or partners.
        | Typically used for refunds, vendor payouts, or commissions.
        | Each provider must define:
        | - key: API key for authentication
        | - secret: API secret/token
        | - webhook: Optional callback URL for events like payout success/failure
        | - provider: Laravel ServiceProvider
        |
        */
        'payout' => [
            'razorpay' => [
                'key'      => env('RAZORPAY_PAYOUT_KEY', ''),
                'secret'   => env('RAZORPAY_PAYOUT_SECRET', ''),
                'webhook'  => env('RAZORPAY_PAYOUT_WEBHOOK', ''),
                'api'      => [
                    'test'    => 'https://api.razorpay.com/v1/',
                    'prod'    => 'https://api.razorpay.com/v1/'
                ],
                'provider' => \Mintreu\LaravelIntegration\Providers\Payout\Razorpay\RazorpayPayoutProvider::class
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | SMS Gateway Providers
        |--------------------------------------------------------------------------
        |
        | Providers used to send SMS notifications to your users.
        | Each provider must define:
        | - key: API key to authenticate requests
        | - secret: API secret or token
        | - webhook: Optional callback URL for delivery reports
        | - provider: Laravel ServiceProvider for integration
        |
        */
        'sms' => [
            'fast2sms' => [
                'key'      => env('FAST2SMS_KEY', ''),
                'secret'   => env('FAST2SMS_SECRET', ''),
                'webhook'  => env('FAST2SMS_WEBHOOK', ''),
                'provider' => \Mintreu\LaravelIntegration\Providers\Sms\Fast2Sms\Fast2SmsProvider::class,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Shipping / Courier Providers
        |--------------------------------------------------------------------------
        |
        | Providers used to manage shipping, track orders, and generate labels.
        | Each provider must define:
        | - key: API key
        | - secret: API secret
        | - webhook: Optional URL for tracking updates
        | - provider: Laravel ServiceProvider class
        |
        */
        'shipping' => [
            'shiprocket' => [
                'key'      => env('SHIPROCKET_KEY', ''),
                'secret'   => env('SHIPROCKET_SECRET', ''),
                'webhook'  => env('SHIPROCKET_WEBHOOK', ''),
                'provider' => \Mintreu\LaravelIntegration\Providers\Shipping\ShipRocket\ShipRocketServiceProvider::class,
                'supports_cod' => true,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Hotel / Booking Providers
        |--------------------------------------------------------------------------
        |
        | Providers to manage hotel bookings or service reservations.
        | Each provider must define:
        | - key: API key for authentication
        | - secret: API secret/token
        | - webhook: Optional callback for booking events
        | - provider: Laravel ServiceProvider
        |
        */
        'booking' => [
//            'hotel' => [
//                'key'      => env('HOTEL_BOOKING_KEY', ''),
//                'secret'   => env('HOTEL_BOOKING_SECRET', ''),
//                'webhook'  => env('HOTEL_BOOKING_WEBHOOK', ''),
//                'provider' => \Vendor\LaravelBooking\HotelBookingServiceProvider::class,
//            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache & Refresh Settings (Optional)
    |--------------------------------------------------------------------------
    |
    | Manage caching for active service providers to improve performance.
    | - active_providers_key: Cache key used to store active providers
    | - ttl_minutes: Duration in minutes to keep the cache
    |
    | Use the artisan command to refresh cache if providers change:
    | php artisan providers:refresh
    |
    */
    'cache' => [
        'active_providers_key' => 'active_providers',
        'ttl_minutes'          => 300,
    ],

];
