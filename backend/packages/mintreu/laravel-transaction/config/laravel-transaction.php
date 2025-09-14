<?php

/*
|--------------------------------------------------------------------------
| Laravel Transaction Package Configuration
|--------------------------------------------------------------------------
|
| This file contains the configuration options for the
| "Mintreu/LaravelTransaction" package. You can use these
| options to customize how transactions and wallet integration
| work in your application.
|
*/

return [


    'callback' => [

        /*
        |--------------------------------------------------------------------------
        | Callback Route Configuration
        |--------------------------------------------------------------------------
        |
        | This section configures the routes that payment providers will call
        | after a transaction is completed or failed. These URLs are used as
        | the `successUrl` and `failureUrl` when creating transactions.
        |
        | - prefix : The route prefix for all callback routes. Default is '_transaction'.
        |            For example, the full success URL will be:
        |            /_transaction/validate/{transaction:uuid}
        |
        | - middleware : Optional array of middleware to apply to callback routes.
        |                For example, you can enforce authentication, throttle,
        |                or custom security middleware.
        |
        | Developers can override the prefix or middleware globally via this config,
        | or pass custom URLs per transaction when calling createDebitTransaction()
        | or createCreditTransaction().
        |
        */
        'prefix' => '_transaction',
        'middleware' => [
            // add your middleware, e.g. 'api', 'auth:sanctum', etc.
        ]
    ],














    /*
    |--------------------------------------------------------------------------
    | Wallet Integration Settings
    |--------------------------------------------------------------------------
    |
    | If your application uses a wallet system (for example: user balance,
    | credits, or an internal wallet feature), you can enable it here.
    |
    | - status : when set to true, the `wallet_id` column and relationship
    |            will be available in the transactions table and model.
    |
    | - model  : defines which Wallet model should be used for the relation.
    |            By default, the package provides its own:
    |            Mintreu\LaravelTransaction\Models\Wallet::class
    |
    |            You can replace it with your own model, for example:
    |            App\Models\Wallet::class
    |
    | - table  : the database table name for your wallet model
    |            (default: "wallets").
    |
    | When status is set to false, the package will ignore wallet integration
    | and no wallet relationship will be applied.
    |
    */

    'wallet' => [

        // Enable or disable wallet support
        'status' => true,

        // Wallet model class to be used (default: package Wallet model)
        'model'  => Mintreu\LaravelTransaction\Models\Wallet::class,

        // Database table name for the wallet model
        'table'  => 'wallets',
    ],

];
