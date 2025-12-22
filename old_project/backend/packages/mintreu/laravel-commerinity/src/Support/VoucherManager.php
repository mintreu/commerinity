<?php

namespace Mintreu\LaravelCommerinity\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\LaravelProductCatalogue\Models\Filter;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;

class VoucherManager
{


    protected array $category;
    protected $filterGroup;
    protected array|\Illuminate\Database\Eloquent\Collection $filters;

    public function __construct()
    {
        $this->category = Category::with('children', 'parent')->where('status', true)->pluck('name', 'id')->toArray();
        $this->filterGroup = FilterGroup::all()->pluck('name', 'id')->toArray();
        $this->filters = Filter::with('options')->has('options')->get();

    }

    public static function make(): static
    {
        return new static ();
    }


    public function getCondition(): array|Collection
    {

        $collection = collect([
            [
                'key' => 'cart',
                'label' => trans('admin::app.promotions.cart-rules.cart-attribute'),
                'children' => $this->getCartRelatedChildren(),
            ],
            [
                'key' => 'cart_item',
                'label' => trans('admin::app.promotions.cart-rules.cart-item-attribute'),
                'children' => $this->getCartItemRelatedChildren(),
            ],
            [
                'key' => 'product',
                'label' => trans('admin::app.promotions.cart-rules.product-attribute'),
                'children' => $this->getProductRelatedChildren(),
            ],
        ]);

        $conditions = collect();
        $conditions = $collection->map(function ($item, $key) use ($conditions) {
            return $conditions->merge($item['children']);
        });

        return collect(array_merge($conditions[0]->toArray(), $conditions[1]->toArray(), $conditions[2]->toArray()));

    }

    protected function getOperator(string $operator_type): array
    {
        return match ($operator_type) {
            'text' => [
                '{}' => 'Contain',
                '!{}' => 'Not Contain',
            ],
            'number', 'price', 'integer', 'decimal' => [
                '==' => 'Equal With',
                '!=' => 'Not Equal',
                '>' => 'Greater than',
                '<' => 'Less than',
                '>=' => 'Greater than or Equal',
                '<=' => 'Less than or Equal',

            ],
            'select', 'multiselect' => [
                '==' => 'Equal With',
                '!=' => 'Not Equal',
            ],
            default => [],
        };
    }

    private function getCartRelatedChildren(): array
    {
        return [
            [
                'key' => 'cart|subTotal',
                'type' => 'price',
                'operator' => $this->getOperator('price'),
                'label' => trans('Cart::SubTotal'),
            ],
            [
                'key' => 'cart|totalQuantity',
                'type' => 'integer',
                'operator' => $this->getOperator('integer'),
                'label' => trans('Cart::Total Item Qty'),
            ],
        ];

    }

    private function getCartItemRelatedChildren(): array
    {
        return [
            [
                'key' => 'cart_item|quantity',
                'type' => 'integer',
                'operator' => $this->getOperator('integer'),
                'label' => trans('CartItem::Qty In Cart'),
            ],
        ];
    }

    private function getProductRelatedChildren(): array
    {
        $productArray = [
            [
                'key' => 'product|category_id',
                'type' => 'multiselect',
                'operator' => $this->getOperator('multiselect'),
                'label' => trans('Product::Categories'),
                'options' => $this->category,
            ],
            [
                'key' => 'product|children::category_id',
                'type' => 'multiselect',
                'operator' => $this->getOperator('multiselect'),
                'label' => trans('Product::Categories(Children)'),
                'options' => Category::where('parent_id','!=',null)->pluck('name', 'id')->toArray(),
            ],
            [
                'key' => 'product|parent::category_id',
                'type' => 'multiselect',
                'operator' => $this->getOperator('multiselect'),
                'label' => trans('Product::Categories(Parent)'),
//                'options' => Category::Parents()->pluck('name', 'id')->toArray(),
                'options' => Category::where('parent_id',null)->pluck('name', 'id')->toArray(),
            ],
        ];

        return array_merge($productArray, $this->getAttributeList());
    }

    private function getAttributeList(): array
    {
        $attrBag = [];
        $allAttribute = $this->filters;

        foreach ($allAttribute as $attr) {
            $key = 'product|'.$attr->code;
            $attrBag[] = [
                'key' => Str::lower($key),
                'type' => $attr->type,
                'operator' => $this->getOperator(Str::lower($attr->type)),
                'label' => trans('Product::'.Str::ucfirst($attr->name)),
                'options' => $attr->options->pluck('display_name', 'display_name')->toArray() ?? [],
            ];
        }

        return $attrBag;
    }





}
