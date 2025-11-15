<?php

namespace App\Services\ProductService\Support\Trait;

use Illuminate\Support\Str;

trait HasProductPreparation
{


    protected function prepareDataForProductCreation(array $data):array
    {
        return array_merge($data,[
//            'vendor_id' => auth()->user()->id,
            'url' => Str::slug($data['name'].'-'.now())
        ]);
    }


    protected function getCleanProductData(array $data)
    {
        unset($data['filter_attributes']);
        return $data;
    }



}
