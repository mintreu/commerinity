<?php

namespace Mintreu\LaravelProductCatalogue\Services;


use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelProductCatalogue\Services\Support\HasProductSupport;
use Mintreu\Toolkit\Casts\PublishableStatusCast;


class ProductCreationService
{
    use HasProductSupport;

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
            'status' =>  $this->data['status'] ?? PublishableStatusCast::DRAFT,
            'type' => $case->value,
            'filter_group_id' => $this->data['filter_group_id'],
            'min_quantity' => $this->data['min_quantity'] ?? 1,
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

        return $this->generateProductVariants($parentProduct);
    }








}
