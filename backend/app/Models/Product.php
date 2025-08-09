<?php

namespace App\Models;

use App\Models\Traits\Cart\HasCartable;

class Product extends \Mintreu\LaravelProductCatalogue\Models\Product
{
    use HasCartable;

}
