<?php

namespace App\Services\CartService;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Mintreu\LaravelMoney\LaravelMoney;

/**
 * Class Cart
 *
 * Adds structured metadata presentation on top of CartService.
 * Designed for clean API formatting in production applications.
 */
class Cart extends CartService
{
    protected ?Collection $cartItems = null;
    /**
     * Get full structured metadata for the cart.
     *
     * @return array
     */
    public function getMeta(): array
    {
        // Eager load cartable with media to avoid N+1 problem
        $this->cartItems = $this->items();
        if ($this->cartItems)
        {
            $this->cartItems->loadMissing('cartable.media');
        }


        return [
            'summary'  => $this->calculateCart(),
            'customer' => $this->getCustomerMeta(),
            'items'    => $this->formatItems(),
            'error'    => $this->error,
        ];
    }

    /**
     * Return structured customer identity data, supporting both guest and authenticated users.
     *
     * @return array
     */
    private function getCustomerMeta(): array
    {
        return [
            'identity' => [
                'type'            => $this->customer ? 'authenticated' : 'guest',
                'is_guest'        => !$this->customer,
                'token_expires_in'=> $this->tokenTTL,
            ],
            'profile' => [
                'name'         => $this->customer?->name,
                'email'        => $this->customer?->email,
                'mobile'       => $this->customer?->mobile,
                'status_label' => $this->customer?->status->getLabel(),
                'type_label'   => $this->customer?->type->getLabel(),
                'class'        => $this->customer
                    ? Str::afterLast(get_class($this->customer), '\\')
                    : null,
            ]
        ];
    }


    /**
     * Compute cart totals.
     *
     * @return array
     */
    private function calculateCart(): array
    {
        $subTotal = new LaravelMoney();
        $tax = new LaravelMoney();
        $discount = new LaravelMoney();
        $total = new LaravelMoney();
        if ($this->cartItems)
        {

            foreach ($this->cartItems as $item) {
                if (!$item->cartable) continue;

                $linePrice = LaravelMoney::make($item->cartable->price)->multiplyOnce($item->quantity);
                $subTotal->add($linePrice);
            }
        }


        $total->add($subTotal)->add($tax)->subtract($discount);

        return [
            'sub_total'       => $subTotal->formatted(),
            'tax'             => $tax->formatted(),
            'tax_percentage'  => 0,
            'discount'        => $discount->formatted(),
            'coupon_applied'  => false,
            'coupon_code'     => null,
            'total'           => $total->formatted(),
        ];
    }

    /**
     * Format cart items for API.
     *
     * @return array
     */
    private function formatItems(): array
    {
        if (is_null($this->cartItems))
        {
            return [];
        }

        return $this->cartItems->map(function (\App\Models\Cart $item) {
            $cartable = $item->cartable;

            return [
//                'id'         => $item->id,
                'product_id' => $cartable?->id,
                'product'    => [
                    'name'      => $cartable?->name,
                    'url'      => $cartable?->url,
                    'sku'      => $cartable?->sku,
                    'type'      => $cartable?->type->getLabel(),
                    'min_quantity'      => $cartable?->min_quantity,
                    'max_quantity'      => $cartable?->max_quantity,
                    'price'     => $cartable ? LaravelMoney::make($cartable->price)->formatted() : null,
                    'thumbnail' => $cartable?->getFirstMediaUrl('displayImage') ?: null,
                ],
                'quantity'   => $item->quantity,
                'total'      => $cartable
                    ? LaravelMoney::make($cartable->price)->multiplyOnce($item->quantity)->formatted()
                    : null,
            ];
        })->toArray();
    }











}
