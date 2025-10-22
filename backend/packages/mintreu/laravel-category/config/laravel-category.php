<?php

use Mintreu\LaravelProductCatalogue\Models\Product;
use App\Models\Lifecycle\Level;

// config for Mintreu/LaravelCategory
return [


    'categorized'   => [
        'models'    => [
            Product::class
        ],
    ],




    'voucher' => [
        'status' => true,

        'targets' => [
            // Add your voucher target models here
            Level::class,
        ],
    ],


];
