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
     * @param bool $formatted
     * @return array
     */
    public function getMeta(bool $formatted = true): array
    {
        // Eager load cartable with media to avoid N+1 problem
        $this->cartItems = $this->items();
        if ($this->cartItems)
        {
            $this->cartItems->loadMissing('cartable.media');
        }

       // $this->prepareMeta($formatted);


        return [
            'summary'  => $this->calculateCart($formatted),
            'customer' => $this->getCustomerMeta(),
            'items'    => $this->formatItems($formatted),
            'error'    => $this->error,
        ];
    }


    protected function prepareMeta(bool $formatted = true)
    {
        $bag = $this->cartItems->map(fn(\App\Models\Cart $item) => CartLineService::make($item)->getMeta($formatted));
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
     * @param bool $formatted
     * @return array
     */
    private function calculateCart(bool $formatted = true): array
    {
        if (!is_null($this->getCouponCode()) && !$this->validCoupon)
        {
            $this->setCouponCode($this->getCouponCode());
        }

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
            'sub_total'       => $formatted ? $subTotal->formatted() : $subTotal,
            'tax'             => $formatted ? $tax->formatted() : $tax,
            'tax_percentage'  => 0,
            'discount'        => $formatted ?$discount->formatted() : $discount,
            'coupon_applied'  => $this->validCoupon,
            'coupon_code'     => $this->getCouponCode(),
            'total'           => $formatted ? $total->formatted() : $total,
            'quantity'        =>   $this->cartItems?->sum('quantity') ?? 0
        ];
    }

    /**
     * Format cart items for API.
     *
     * @param bool $formatted
     * @return array
     */
    private function formatItems(bool $formatted = true): array
    {
        if (is_null($this->cartItems))
        {
            return [];
        }

        return $this->cartItems->map(function (\App\Models\Cart $item) use($formatted) {
            $cartable = $item->cartable;

            $subTotal = LaravelMoney::make($cartable->price)->multiplyOnce($item->quantity);
            $tax = new LaravelMoney();
            $discount = new LaravelMoney();
            $total = new LaravelMoney();

            $total = $cartable ? $total->addOnce($subTotal) : $total;

            return [
                'product_id' => $cartable?->id,
                'quantity'   => $item->quantity,
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
                'summary' => [
                    'quantity'   => $item->quantity,
                    'sub_total' => $formatted ? $subTotal->formatted() : $subTotal,
                    'discount'  => $formatted ? $discount->formatted() : $discount,
                    'tax'       => $formatted ? $tax->formatted() : $tax,
                    'total'      => $formatted ? $total->formatted() : $total,
                ],
            ];
        })->toArray();
    }











}
