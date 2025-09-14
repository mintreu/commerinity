<?php

/*
|--------------------------------------------------------------------------
| Laravel Product Catalogue – Sales Configuration
|--------------------------------------------------------------------------
|
| This file controls how the Sales system integrates with the Product
| Catalogue. It is published by the "laravel-commerinity" package, but
| used inside "laravel-product-catalogue".
|
| You can override these values in your main Laravel app by publishing
| this config file:
|
|   php artisan vendor:publish --tag="commerinity-config"
|
| That way, you can swap out models, enable/disable sales, or even
| extend models without changing the package code.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Sales Feature
    |--------------------------------------------------------------------------
    |
    | This flag controls whether the "Sales" feature is active. If set to
    | false, all Sale-related relationships and queries can be skipped.
    |
    */
    'sales' => [

        'enabled' => true,

        /*
        |--------------------------------------------------------------------------
        | Sale Model
        |--------------------------------------------------------------------------
        |
        | This is the main "Sale" model. It represents a sale campaign such as
        | "Diwali Sale", "New Year Discount", etc.
        |
        | You may extend this model in your app and replace it here with your
        | custom class, as long as it extends the base Sale model.
        |
        */
        'model' => \Mintreu\LaravelCommerinity\Models\Sale::class,

        /*
        |--------------------------------------------------------------------------
        | Pivot / Relation Model (SaleProduct)
        |--------------------------------------------------------------------------
        |
        | This model represents the "pivot" between a Sale and a Product.
        | For example, Product A may belong to "Sale 1" with a specific
        | discount amount.
        |
        | You may customize or extend this pivot model in your app and
        | override it here.
        |
        */
        'related_model' => \Mintreu\LaravelCommerinity\Models\SaleProduct::class,
    ],

];
