<?php

namespace Mintreu\LaravelCommerinity\Services\CartService;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Mintreu\LaravelGeokit\Models\Address;
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
     * @param Address|null $customerAddress
     * @return array
     */
    public function getMeta(bool $formatted = false, ?Address $customerAddress = null): array
    {

        // Eager load cartable with media to avoid N+1 problem
        $this->cartItems = $this->items();

        $itemMeta = null;
        if ($this->cartItems)
        {
            $this->cartItems->load('cartable');
            // Note: cheapestTier is being deprecated in favor of StockLocatorService,
            // but left for now as per instructions.
            $this->cartItems->loadMissing([
                'cartable.media',
                'cartable.cheapestTier',
                'cartable.sales' => fn($query) => $query
                    ->where('starts_from', '<=', now())
                    ->where('ends_till', '>=', now())
                ,

            ]);

            $voucherValidator = CartVoucherValidator::make($this, $this->getCouponCode(), $this->customer);

            $cartMeta = $this->prepareMeta($voucherValidator, $formatted, $customerAddress);

           $itemMeta = $cartMeta;

        }



        return [
            'summary'  => $this->getSummaryMeta($itemMeta,$formatted),
            'customer' => $this->getCustomerMeta(),
            'items'    => $itemMeta,
            'error'    => $this->error,
        ];
    }


    protected function prepareMeta(null|CartVoucherValidator $voucherValidator, bool $formatted = false, ?Address $customerAddress = null): array
    {
        return $this->cartItems->map(fn($item) => CartLineService::make(cartService: $this, lineItem:  $item, customerAddress: $customerAddress, voucherValidator: $voucherValidator)->getMeta($formatted))->toArray();
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




    private function getSummaryMeta(array $itemsMeta = [], bool $formatted = false):array
    {
        $subTotal = new LaravelMoney();
        $tax = new LaravelMoney();
        $discount = new LaravelMoney();
        $total = new LaravelMoney();



        if (!empty($itemsMeta))
        {

            foreach ($itemsMeta as $item) {
                if (!isset($item['product']['instance'])) continue;
                $subTotal->add($item['summary']['raw']['sub_total']);
                $discount->add($item['summary']['raw']['discount']);
                $tax->add($item['summary']['raw']['tax']);
                $total->add($item['summary']['raw']['total']);
            }
        }


      //  $total->add($subTotal)->add($tax)->subtract($discount);

        return [
            'sub_total'       => $formatted ? $subTotal->getAmount() : $subTotal,
            'shipping_cost'   => $formatted ? LaravelMoney::make(0)->getAmount() : LaravelMoney::make(0),
            'tax'             => $formatted ? $tax->getAmount() : $tax,
            'tax_percentage'  => 0,
            'discount'        => $formatted ?$discount->getAmount() : $discount,
            'coupon_applied'  => $this->validCoupon,
            'coupon_code'     => $this->getCouponCode(),
            'total'           => $formatted ? $total->getAmount() : $total,
            'quantity'        =>   $this->cartItems?->sum('quantity') ?? 0,
            'formatted'       => [
                'sub_total'       => $formatted ? $subTotal->formatted() : $subTotal,
                'shipping_cost'   => null,
                'tax'             => $formatted ? $tax->formatted() : $tax,
                'tax_percentage'  => 0,
                'discount'        => $formatted ?$discount->formatted() : $discount,
                'coupon_applied'  => $this->validCoupon,
                'coupon_code'     => $this->getCouponCode(),
                'total'           => $formatted ? $total->formatted() : $total,
                'quantity'        =>   $this->cartItems?->sum('quantity') ?? 0,
            ]
        ];
    }












//    /**
//     * Compute cart totals.
//     *
//     * @param bool $formatted
//     * @return array
//     */
//    private function calculateCart(bool $formatted = true): array
//    {
//        if (!is_null($this->getCouponCode()) && !$this->validCoupon)
//        {
//            $this->setCouponCode($this->getCouponCode());
//        }
//
//        $subTotal = new LaravelMoney();
//        $tax = new LaravelMoney();
//        $discount = new LaravelMoney();
//        $total = new LaravelMoney();
//        if ($this->cartItems)
//        {
//
//            foreach ($this->cartItems as $item) {
//                if (!$item->cartable) continue;
//
//                $linePrice = LaravelMoney::make($item->cartable->price)->times($item->quantity);
//                $subTotal->add($linePrice);
//            }
//        }
//
//
//        $total->add($subTotal)->add($tax)->subtract($discount);
//
//        return [
//            'sub_total'       => $formatted ? $subTotal->formatted() : $subTotal,
//            'tax'             => $formatted ? $tax->formatted() : $tax,
//            'tax_percentage'  => 0,
//            'discount'        => $formatted ?$discount->formatted() : $discount,
//            'coupon_applied'  => $this->validCoupon,
//            'coupon_code'     => $this->getCouponCode(),
//            'total'           => $formatted ? $total->formatted() : $total,
//            'quantity'        =>   $this->cartItems?->sum('quantity') ?? 0
//        ];
//    }













}
