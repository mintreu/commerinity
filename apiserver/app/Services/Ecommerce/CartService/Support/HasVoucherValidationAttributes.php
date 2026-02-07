<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\CartService\Support;

use App\Models\Ecommerce\Product;

trait HasVoucherValidationAttributes
{
    protected array $blackListCartAttributes = ['postcode', 'state', 'country', 'shipping_method', 'payment_method'];
    protected array $meta = [];

    protected function getAttributeValue(array $condition, Product $product): mixed
    {
        $chunks = explode('|', $condition['attribute'] ?? '');
        $attributeNameChunks = explode('::', $chunks[1] ?? '');
        $attributeCode = count($attributeNameChunks) > 1
            ? $attributeNameChunks[count($attributeNameChunks) - 1]
            : ($attributeNameChunks[0] ?? '');

        return match ($chunks[0] ?? '') {
            'cart' => $this->getCartAttributeValue($attributeCode),
            'cart_item', 'product' => $this->getProductAttributeValue($attributeCode, $product),
            default => null,
        };
    }

    private function getCartAttributeValue(string $attributeCode): mixed
    {
        if (in_array($attributeCode, $this->blackListCartAttributes, true)) {
            return null;
        }

        return $this->meta[$attributeCode] ?? null;
    }

    private function getProductAttributeValue(string $attributeCode, Product $product): mixed
    {
        if ($attributeCode === 'category_id') {
            return $product->category_id ? [$product->category_id] : [];
        }

        $value = $product->{$attributeCode} ?? $product->{ucfirst($attributeCode)} ?? null;

        if ($attributeCode === 'price') {
            return $product->getPrice();
        }

        if (is_string($value)) {
            $parts = explode(',', $value);
            if (isset($parts[1]) && $parts[1] !== '') {
                return $parts;
            }

            return $parts[0];
        }

        return $value;
    }
}
