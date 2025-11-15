<?php

namespace App\Services\ProductService;


use App\Models\Enums\Shop\ProductTypeCast;
use App\Models\Store\Product\Product;
use App\Services\ProductService\Service\Bundle;
use App\Services\ProductService\Service\Configurable;
use App\Services\ProductService\Service\Simple;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class ProductManageService
{

    protected array $data = [];
    protected string $type;

    public function __construct(array $data)
    {
        $this->type = $data['type'];
        $this->data = $data;

        throw_unless(in_array($this->type, collect(ProductTypeCast::cases())->map(fn($case) => $case->value)->toArray()),'undefined product type selected');

    }


    public static function make(array $data):static
    {
        return new static($data);
    }



    /**
     * @throws Throwable
     */
    public function create(): Product|bool|null
    {
        return match($this->type) {
            ProductTypeCast::SIMPLE->value => $this->getSimpleProduct()->create($this->data),
            ProductTypeCast::CONFIGURABLE->value => $this->getConfigurableProduct()->create($this->data),
            ProductTypeCast::BUNDLE->value => $this->getBundleProduct()->create($this->data),
        };
    }


    public function edit(Model|Product $product): Product|bool|null
    {
        return match($this->type) {
            ProductTypeCast::SIMPLE->value => $this->getSimpleProduct()->edit($product,$this->data),
            ProductTypeCast::CONFIGURABLE->value => $this->getConfigurableProduct()->edit($product,$this->data),
            ProductTypeCast::BUNDLE->value => $this->getBundleProduct()->edit($product,$this->data),
        };
    }









    protected function getSimpleProduct(): Simple
    {
        return new Simple();
    }

    protected function getConfigurableProduct(): Configurable
    {
        return new Configurable();
    }

    protected function getBundleProduct(): Bundle
    {
        return new Bundle();
    }



}
