<?php

namespace Mintreu\LaravelCommerinity\Services\CartService\Support;

use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelProductCatalogue\Models\Product;

trait HasVoucherValidationAttributes
{

    protected array $backListCartAttributes = ['postcode', 'state', 'country', 'shipping_method', 'payment_method'];

    protected function getAttributeValue(array $condition, Model|Product $product)
    {
        $chunks = explode('|', $condition['attribute']);

        $attributeNameChunks = explode('::', $chunks[1]);

        $attributeCode = (\count($attributeNameChunks) > 1) ? $attributeNameChunks[\count($attributeNameChunks) - 1] : $attributeNameChunks[0];



        // Compare and Validate
        switch (current($chunks)) {
            // $cart+
            case 'cart':
                return $this->getCartAttributeValue($attributeCode);
                break;
            // customer->cart->each->pivot
            case 'cart_item':
                return $this->getCartItemAttributeValue($attributeCode, $product);
                break;
            // customer->cart-each
            case 'product':
                return $this->getProductAttributeValue($attributeCode, $product, $condition);
                break;
            default:
                break;
        }
    }


    private function getCartAttributeValue(string $attributeCode)
    {
        if (! in_array($attributeCode, $this->backListCartAttributes))
        {
            return $this->meta[$attributeCode] ?? null;
        }
        return null;
    }

    private function getCartItemAttributeValue(string $attributeCode, Product $product)
    {
//        if (isset($product->pivot->{$attributeCode}))
//        {
//            return $product->pivot->{$attributeCode};
//        }elseif (isset($product->{$attributeCode}))
//        {
//            return $product->{$attributeCode};
//        }
//        return null;
        return isset($product->pivot->{$attributeCode}) ? $product->pivot->{$attributeCode} : null;
    }

    private function getProductAttributeValue(string $attributeCode, Product $product, array $condition)
    {

        if ($attributeCode == 'category_id') {
            return $product->categories()->pluck('id')->toArray();
        } else {

            $value = null;
            if (isset($product->{$attributeCode})) {
                $value = $product->{$attributeCode};
            } elseif (isset($product->{ucfirst($attributeCode)})) {
                $value = $product->{ucfirst($attributeCode)};
            }

            // Special Case
            if (!is_null($value) && $attributeCode == 'quantity')
            {

                $value = $product->availableStocks()->count();

            }

            if ($value) {

                if (! is_string($value)) {
                    return $value;
                } else {
                    $chunk = explode(',', $value);

                    if (isset($chunk[1]) && ! empty($chunk[1])) {
                        return $chunk;
                    } else {
                        return $chunk[0];
                    }
                }

            }

        }

        return null;
    }


}
