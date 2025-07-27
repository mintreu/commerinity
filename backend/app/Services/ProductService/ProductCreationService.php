<?php

namespace App\Services\ProductService;

use App\Casts\ModelStatusCast;
use App\Casts\ProductTypeCast;
use App\Models\FilterOption;
use App\Models\Product;

class ProductCreationService
{

    protected array $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        // Remove null values from 'filter_options' if it exists and is an array
        if (isset($data['filter_options']) && is_array($data['filter_options'])) {
            $data['filter_options'] = array_filter($data['filter_options'], function ($value) {
                return !is_null($value);
            });
        }
        $this->data = $data;

    }


    public static function make(array $data): static
    {
        return new static($data);
    }



    public function create():?Product
    {
        $type = $this->data['type'] instanceof ProductTypeCast  ? $this->data['type'] : ProductTypeCast::from($this->data['type']);
        return match ($type)
        {
            ProductTypeCast::SIMPLE => $this->makeProduct(ProductTypeCast::SIMPLE),
            ProductTypeCast::WHOLESALE => $this->makeProduct(ProductTypeCast::WHOLESALE),
            ProductTypeCast::CONFIGURABLE => $this->createConfigurableProduct(),
        };
    }


    private function makeProduct(ProductTypeCast $case):Product
    {
        $fillable = $this->data;
        unset($fillable['filter_options']);
        $product = Product::create(array_merge($fillable,[
            'name' => $this->data['name'],
            'sku' => $this->data['sku'],
            'url' => $this->data['url'],
            'status' =>  $this->data['status'] ?? ModelStatusCast::DRAFT,
            'type' => $case->value,
            'filter_group_id' => $this->data['filter_group_id'],
        ]));
        // Attach filter options for simple product
        if (! empty($this->data['filter_options'])) {
            if ($case == ProductTypeCast::CONFIGURABLE)
            {
                $this->attachFilterOptionsToParent($product, $this->data['filter_options']);
            }else{
                $this->attachFilterOptionsToProduct($product, $this->data['filter_options']);
            }

        }

        return $product;
    }




    private function createConfigurableProduct():Product
    {
        // Create parent product
        $parentProduct = $this->makeProduct(ProductTypeCast::CONFIGURABLE);

        // Generate and create variants
        if (! empty($this->data['filter_options'])) {
            $variants = $this->generateVariants($this->data['sku'], $this->data['filter_options']);
            $this->createVariants($parentProduct, $this->data, $variants);
        }

        return $parentProduct;
    }







    /**
     * ***************
     * Helpers Methods
     * *************************************************
     * ************************************************************
     */

    /**
     * Attach filter options to parent configurable product
     */
    private function attachFilterOptionsToParent(Product $product, array $filterOptions): void
    {
        foreach ($filterOptions as $filterId => $optionIds) {
            foreach ($optionIds as $optionId) {
                $product->filterOptions()->attach($optionId, ['filter_id' => $filterId]);
            }
        }
    }


    /**
     * Attach filter options to product (for simple products or variants)
     */
    private function attachFilterOptionsToProduct(Product $product, array $filterOptions): void
    {

        // Handle both single filter options (variants) and multiple filter options (simple products)
        if (is_numeric(array_key_first($filterOptions))) {
            // Variant case: array of option IDs
            foreach ($filterOptions as $optionId) {
                // Ensure we're working with just the ID
                if (is_object($optionId)) {
                    $optionId = $optionId->id;
                } elseif (is_array($optionId)) {
                    //$optionId = $optionId['id'] ?? $optionId[0];
                    $optionId = $product->type == ProductTypeCast::CONFIGURABLE ? $optionId[0] : $optionId;
                }

                $option = FilterOption::with('filter')->find($optionId);

                if ($option && $option->filter) {
                    $product->filterOptions()->attach($optionId, ['filter_id' => $option->filter->id]);
                }
            }
        } else {
            // Simple product case: filter_id => option_ids mapping
            foreach ($filterOptions as $filterId => $optionIds) {
                $optionIds = is_array($optionIds) ? $optionIds : [$optionIds];
                foreach ($optionIds as $optionId) {
                    // Ensure we're working with just the ID
                    if (is_object($optionId)) {
                        $optionId = $optionId->id;
                    } elseif (is_array($optionId)) {
                        $optionId = $optionId['id'] ?? $optionId[0];
                    }

                    $product->filterOptions()->attach($optionId, ['filter_id' => $filterId]);
                }
            }
        }
    }




    /**
     * Generate all possible variants based on selected filters (Cartesian Product)
     */
    private function generateVariants(string $initialSku, array $filters): array
    {
        // Get all selected filter option IDs and their corresponding values
        $filterOptionsWithValues = [];
        foreach ($filters as $filterId => $optionIds) {
            $options = FilterOption::whereIn('id', $optionIds)
                ->pluck('value', 'id')
                ->map(function ($value) {
                    // Clean up the value by removing 'GSM' and any extra spaces
                    return trim(str_replace('GSM', '', $value));
                })
                ->toArray();
            $filterOptionsWithValues[$filterId] = $options;
        }

        // Generate Cartesian product
        $combinations = $this->cartesianProduct($filterOptionsWithValues);

        // Format variants with unique SKUs
        return array_map(function ($combination) use ($initialSku) {
            $optionValues = implode('-', array_values($combination));

            return [
                'sku' => strtoupper("{$initialSku}-{$optionValues}"),
                'url' => strtolower(str_replace(' ', '-', "{$initialSku}-{$optionValues}")),
                'filter_option_ids' => array_keys($combination),
            ];
        }, $combinations);
    }



    /**
     * Create variant products
     */
    private function createVariants(Product $parentProduct, array $productData, array $variants): void
    {
        foreach ($variants as $variant) {
            $variantProduct = Product::create([
                'parent_id' => $parentProduct->id,
                'type' => 'simple',
                'name' => $productData['name'],
                'sku' => $variant['sku'],
                'url' => $variant['url'],
                'status' => $parentProduct->status,
                'description' => $productData['description'] ?? null,
                'filter_group_id' => $productData['filter_group_id'],
                'category_id' => $productData['category_id'] ?? null,
            ]);

            // Attach filter options to variant
            $this->attachFilterOptionsToProduct($variantProduct, $variant['filter_option_ids']);
        }
    }



    /**
     * Generates the Cartesian Product of filter options
     */
    private function cartesianProduct(array $arrays): array
    {
        if (empty($arrays)) {
            return [[]];
        }

        $result = [[]];
        foreach ($arrays as $options) {
            $newResult = [];
            foreach ($result as $existing) {
                foreach ($options as $optionId => $optionValue) {
                    $newResult[] = $existing + [$optionId => $optionValue];
                }
            }
            $result = $newResult;
        }

        return $result;
    }




}
