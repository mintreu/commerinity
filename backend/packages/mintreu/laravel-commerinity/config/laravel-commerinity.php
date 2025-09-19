<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product Model Configuration
    |--------------------------------------------------------------------------
    |
    | Define the Eloquent model that represents your products. By default,
    | this uses the Product model from the Laravel Product Catalogue package.
    | You may replace this with your own implementation if needed.
    |
    */

    'product' => [
        'model' => \Mintreu\LaravelProductCatalogue\Models\Product::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Sales Configuration
    |--------------------------------------------------------------------------
    |
    | Manage settings related to Sales in your store.
    |
    | "status" determines whether the Sales feature is enabled or disabled.
    |
    | "targets" defines the models that a Sale can be applied to. These should
    | be fully qualified model class names. For example, you may want to attach
    | sales to specific User Groups, Customer Groups, Membership Levels, or
    | Communities. The package uses polymorphic relations, so you can extend
    | this list freely based on your application’s needs.
    |
    | Example:
    |   'targets' => [
    |       App\Models\User::class,
    |       App\Models\CustomerGroup::class,
    |       App\Models\Community::class,
    |   ],
    |
    */

    'sales' => [
        'status' => true,

        'targets' => [
            \App\Models\Lifecycle\Level::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Voucher Configuration
    |--------------------------------------------------------------------------
    |
    | Manage settings related to Vouchers in your store.
    |
    | "status" determines whether the Vouchers feature is enabled or disabled.
    |
    | "targets" defines the models that a Voucher can be applied to. Just like
    | sales, this is an array of fully qualified model class names. This allows
    | vouchers to be attached to different entities such as Users, Groups, or
    | custom models defined in your application.
    |
    | Example:
    |   'targets' => [
    |       App\Models\User::class,
    |       App\Models\CustomerGroup::class,
    |   ],
    |
    */

    'voucher' => [
        'status' => true,

        'targets' => [
            // Add your voucher target models here
            \App\Models\Lifecycle\Level::class,
        ],
    ],





    // New: cart configuration (read by CartService)
    'cart' => [
        'guest' => [
            'header_id'          => 'x-guest-id',
            'header_token'       => 'x-guest-token',
            'token_cache_prefix' => 'guest_cart_token_',
            // Token TTL (read as seconds in capture(); friendly days used by ensureGuestCredential)
            'token_ttl_seconds'  => 60 * 60 * 24 * 7, // 7 days
            'token_ttl_days'     => 15,
        ],
        'coupon' => [
            'session_key' => 'cart.coupon', // applied coupon session key
            'ttl_minutes' => 0,              // 0 = persist for session lifetime
        ],
        'limits' => [
            'max_per_order_default' => 1,    // fallback if model doesn't define max_quantity
        ],
    ],


];
