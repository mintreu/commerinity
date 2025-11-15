<?php

namespace App\Services\ProductService\Service;


use App\Models\Store\Product\Product;
use App\Services\ProductService\Support\Contract\ProductCreationContract;

class Bundle implements ProductCreationContract
{

    /**
     * @param array $data
     * @return bool|Product
     */
    public function create(array $data): bool|Product
    {
        // TODO: Implement create() method.
    }




    public function edit(Product $product,array $data):bool|Product
    {
        dd($this,$data,$product);
    }


}
