<?php

namespace App\Services\ProductService\Support\Contract;



use App\Models\Store\Product\Product;

interface ProductCreationContract
{

    public function create(array $data):bool|Product;



}
