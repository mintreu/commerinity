<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default shipment provider
    |--------------------------------------------------------------------------
    |
    | The code used by ShipmentService when no provider is explicitly passed.
    | Keep this as "native" until an external provider is integrated.
    |
    */
    'default_provider' => env('SHIPPING_PROVIDER', 'native'),

    /*
    |--------------------------------------------------------------------------
    | Native provider pricing rules
    |--------------------------------------------------------------------------
    |
    | Shipping charges for the in-house (native) provider follow a flat-rate
    | slab for the first X kilograms and an incremental per-kg rate thereafter.
    | Amounts are stored in paise to avoid floating point calculations.
    |
    */
    'native' => [
        // Base amount charged for shipments up to the base_weight_grams threshold.
        'base_rate_paise' => (int) env('SHIPPING_NATIVE_BASE_RATE', 5000), // ₹50.00

        // Maximum weight (in grams) covered by the base rate.
        'base_weight_grams' => (int) env('SHIPPING_NATIVE_BASE_WEIGHT', 1000), // 1kg

        // Incremental charge (in paise) applied per additional kilogram after base weight.
        'rate_per_kg_paise' => (int) env('SHIPPING_NATIVE_RATE_PER_KG', 2000), // ₹20 / kg

        // Fallback per-item weight when explicit product weight data is unavailable.
        'default_item_weight_grams' => (int) env('SHIPPING_NATIVE_DEFAULT_ITEM_WEIGHT', 500), // 0.5kg
    ],

    /*
    |--------------------------------------------------------------------------
    | Shiprocket provider configuration
    |--------------------------------------------------------------------------
    |
    | Centralizes all credentials and defaults required for the Shiprocket
    | integration. Toggle `enabled` to true and provide the required env
    | variables to activate this provider via ShipmentService.
    |
    */
    'shiprocket' => [
        'enabled' => (bool) env('SHIPROCKET_ENABLED', false),
        'base_url' => env('SHIPROCKET_BASE_URL', 'https://apiv2.shiprocket.in/v1/external/'),
        'email' => env('SHIPROCKET_EMAIL'),
        'password' => env('SHIPROCKET_PASSWORD'),
        'channel_id' => env('SHIPROCKET_CHANNEL_ID'),
        'pickup_code' => env('SHIPROCKET_PICKUP_CODE', 'Primary'),
        'token_ttl_minutes' => (int) env('SHIPROCKET_TOKEN_TTL', 50),
        'default_dimensions_cm' => [
            'length' => (int) env('SHIPROCKET_DEFAULT_LENGTH_CM', 10),
            'breadth' => (int) env('SHIPROCKET_DEFAULT_BREADTH_CM', 10),
            'height' => (int) env('SHIPROCKET_DEFAULT_HEIGHT_CM', 5),
        ],
        'default_item_weight_grams' => (int) env('SHIPROCKET_DEFAULT_ITEM_WEIGHT', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider labels/metadata
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'native' => [
            'label' => 'Native Logistics',
            'enabled' => true,
        ],
        'shiprocket' => [
            'label' => 'Shiprocket',
            'enabled' => (bool) env('SHIPROCKET_ENABLED', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Shipping method presets
    |--------------------------------------------------------------------------
    |
    | Shared list of shipping method codes => labels exposed in Filament and
    | API payloads. Extend this list as additional modes are supported.
    |
    */
    'methods' => [
        'standard' => 'Standard Delivery (Surface)',
        'express' => 'Express Delivery (Air)',
    ],
];
