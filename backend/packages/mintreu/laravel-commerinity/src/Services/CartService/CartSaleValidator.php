<?php

namespace Mintreu\LaravelCommerinity\Services\CartService;

use App\Models\Cart as CartModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelCommerinity\Models\SaleProduct;
use Mintreu\LaravelMoney\LaravelMoney;

class CartSaleValidator
{

    protected CartService $cartService;
    protected Model $cartable;
    protected null|int|LaravelMoney $discount = null;
    protected ?SaleProduct $applicableSale = null;
    protected int $resolveProductPrice = 0;
    protected ?Collection $sales = null;

    /**
     * @param CartService $cartService
     * @param CartModel $lineItem
     */
    public function __construct(CartService $cartService,CartModel $lineItem)
    {
        $this->cartService = $cartService;
        $this->cartable = $lineItem->cartable;
        $this->sales = $this->cartable?->sales;
        $this->sales?->loadMissing('sale');
    }


    public static function make(CartService $cartService,CartModel $lineItem):static
    {
        return new static($cartService,$lineItem);
    }

    public function setResolvedPrice(int $resolvePrice = 0): static
    {
        $this->resolveProductPrice = $resolvePrice;
        return $this;
    }


    public function validate():bool
    {
        return $this->validateSale();
    }


    public function toArray(): array
    {
        return [
            'sales' => [
                'applicable' => [
                    'name' =>  $this->applicableSale?->sale?->name ?? null,
                    'sale_price' =>  LaravelMoney::format($this->applicableSale?->sale_price) ?? null,
                    'discount'  => $this->applicableSale?->action_type?->getUnit($this->applicableSale?->discount_amount),
                    'ends_till' =>  $this->applicableSale?->ends_till ?? null,
                    'saleProduct' => $this->applicableSale ?? null,
                ],
                'available'  => $this->sales
            ]
        ];
    }










    private function validateSale():bool
    {
        if ($this->cartable?->sales)
        {
            $this->sales = $this->cartable?->sales;
            $this->sales->loadMissing('sale');

            $this->discount = LaravelMoney::make(0);
            $endRuleSale = $this->sales->where('end_other_rules',true)->first();
            // always apply max sale discount and list other available for display only
            if ($endRuleSale)
            {
                if($this->isSaleApplicable($endRuleSale))
                {
                    $this->applicableSale = $endRuleSale;
                    $this->discount->add($endRuleSale->sale_price);
                    return true;
                }
            }else{
                foreach ($this->sales->sortBy('sort_order') as $sale)
                {
                    if($this->isSaleApplicable($endRuleSale))
                    {
                        $this->applicableSale = $sale;
                        $this->discount->add($sale->sale_price);
                        return true;
                    }
                }
            }
        }

        return false;
    }



    private function isSaleApplicable(SaleProduct $saleProduct): bool
    {
        $conditionType = $saleProduct->sale->condition_type; // true = AND, false = OR
        $conditions = $saleProduct->sale->conditions ?? [];

        if (empty($conditions)) {
            // agar koi condition hi nahi hai to sale directly applicable
            return true;
        }

        if ($conditionType) {
            // AND logic → sab true hone chahiye
            foreach ($conditions as $condition) {
                if (! $this->validateSaleCondition($condition)) {
                    return false; // ek bhi fail to sale not applicable
                }
            }
            return true; // sab pass ho gaye
        } else {
            // OR logic → ek bhi true chahiye
            foreach ($conditions as $condition) {
                if ($this->validateSaleCondition($condition)) {
                    return true; // ek match mila to applicable
                }
            }
            return false; // koi bhi pass nahi hua
        }
    }


    private function validateSaleCondition(array $condition): bool
    {
        $chunks = explode('|', $condition['attribute']);
        $field  = $chunks[1] ?? null; // e.g. "price"

        if ($field && isset($this->cartable->{$field})) {
            $cartValue = $this->cartable->{$field};   // e.g. product price
            if ($field == 'price') {
                $cartValue = $this->resolveProductPrice;
            }
            $conditionValue = $condition['value'];    // e.g. "200"
            $operator = $condition['operator'];       // e.g. ">"

            $result = $this->validateCompare($cartValue, $operator, $conditionValue);

            if (! $result) {
                $this->cartService->setError("Condition failed: {$field} {$operator} {$conditionValue}, actual: {$cartValue}");
            }

            return $result;
        }

        $this->cartService->setError("Invalid condition attribute: {$condition['attribute']}");
        return false;
    }



    private function validateCompare($cartValue, $operator, $conditionValue)
    {
        try {
            return match ($operator) {
                '>' => $cartValue > $conditionValue,
                '<' => $cartValue < $conditionValue,
                '>=' => $cartValue >= $conditionValue,
                '<=' => $cartValue <= $conditionValue,
                '==', '=' => $cartValue == $conditionValue,
                '!=', '<>' => $cartValue != $conditionValue,
                default => throw new \InvalidArgumentException("Unsupported operator: {$operator}"),
            };
        } catch (\Throwable $e) {
            $this->cartService->setError("Comparison error: {$e->getMessage()} (cartValue={$cartValue}, operator={$operator}, conditionValue={$conditionValue})");
            return false;
        }
    }





}
