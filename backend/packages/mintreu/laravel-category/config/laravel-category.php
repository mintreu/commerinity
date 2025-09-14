<?php

// config for Mintreu/LaravelCategory
return [


    'categorized'   => [
        'models'    => [
            \Mintreu\LaravelProductCatalogue\Models\Product::class
        ],
    ],




    'voucher' => [
        'status' => true,

        'targets' => [
            // Add your voucher target models here
            \App\Models\Lifecycle\Level::class,
        ],
    ],


];
