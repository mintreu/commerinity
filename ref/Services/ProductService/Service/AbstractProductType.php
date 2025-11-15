<?php

namespace App\Services\ProductService\Service;

use App\Models\Enums\PublishableCast;
use App\Models\Store\Product\Product;

abstract class AbstractProductType
{



    public function edit(Product $product,array $data):bool|Product
    {
        $formData = $data;
        unset($formData['categories']);
        unset($formData['filter_options']);
        unset($formData['flat']);
        unset($formData['type']);
        $formData['status'] = PublishableCast::tryFrom($data['status']);
        $product->update($formData);

        // Update Relations
        $product->categories()->sync($data['categories']);
        // Extract only the IDs from filter options and sync
        if (isset($data['filter_options']))
        {
            $filterOptionIds = array_values($data['filter_options']);
            $product->filterOptions()->sync($filterOptionIds);
        }

        if (isset($data['flat']))
        {
            $product->flat()->update($data['flat']);
        }


        return $product;
    }


}
