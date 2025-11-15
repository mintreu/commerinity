<?php

namespace App\Services\CartService;

use App\Models\Cart\Cart;
use App\Models\Lifecycle\Stage;
use App\Models\Service\Point\ServicePointProduct;
use App\Models\Store\Product\Product;
use App\Services\MoneyService\Money;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class CartService extends CartManager
{


    public function getMeta(): array
    {
        $this->calculateCartMeta();
        return $this->meta;
    }



    protected function calculateCartMeta()
    {
        $bag = [];
        $subTotal = new Money();
        $taxAmount = new Money();

        $totalAmount = new Money();
        $rewardPoints = 0;
        foreach ($this->items() as $item)
        {
            $total = new Money();
            $itemDiscountAmount = new Money(0);
            $billAmount = new Money($item->product->price);
            $billAmount->multiply($item->quantity);
            $subTotal->add($billAmount);

            $billAmount2 = new Money();
            $billAmount2->add($billAmount);

            if ($item->product instanceof Product)
            {
                $itemModel = $item->product;
                $itemModel->loadMissing('category');
                // Tax Calculate From
                $taxPercentage = collect($itemModel->category->tax_info)->first();
                $tax = $billAmount2->multiplyOnce($taxPercentage ?? 0)->divideOnce(100);
            }else{
                $tax = $billAmount2->multiplyOnce($item->product->tax_percent ?? 0)->divideOnce(100);
                $taxPercentage = $item->product->tax_percent;
            }



            $taxAmount->add($tax);

            $total->add($billAmount)->add($tax);




            $totalAmount->add($total);
            $bag [] = [
                'id' => $item->id,
                'item_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->product->price,
                'totals' => [
                    'quantity' => $item->quantity,
                    'subTotal' => $billAmount,
                    'taxAmount' => $tax,
                    'tax_percentage' => $taxPercentage,
                    'discount' => $itemDiscountAmount,
//                    'shipping' => new Money(0),
                    'total' => $total,
                    'display' => [
                        'subTotal' => $billAmount->formatted(),
                        'taxAmount' => $tax->formatted(),
                        'tax_percentage' => $taxPercentage,
                        'discount' => Money::format(0),
//                        'shipping' => Money::format(0),
                        'total' => $total->formatted(),
                    ],
                ],
                'reward_point' =>  $item->product instanceof Product ? $item->product->reward_point * $item->quantity : 0,
//                'type' => match ($item->product_type) {
//                        Stage::class => 'Stage',
//                        ServicePointProduct::class => 'Subscription',
//                        default => 'Product',
//                    },
                'item' => $item
                ];
            if ($item->product instanceof Product)
            {
                $rewardPoints += $item->product->reward_point * $item->quantity;
            }

        }



        // Calculate Shipping Charge (fixed)
        $shippingAmount = $totalAmount->lessThan(Money::make(500.00)) && !$totalAmount->sameAs(0) ? Money::make(60.00) : Money::make();

        $totalAmount->add($shippingAmount);


       $this->meta = [
           'status' => [
               'isEmpty' => $this->isEmpty(),
               'hasChanged' => $this->changed,
           ],
           'error' => $this->getErrors(),
           'customer' => [
               'email' => $this->getCustomer()->email
           ],
           'coupon' => [
               'code' => $this->couponCode,
               'isValid' => $this->validCoupon,
           ],
           'totals' => [
               'quantity' => $this->getTotalQuantity(),
               'subTotal' => $subTotal,
               'discount' => new Money(0),
               'taxAmount' => $taxAmount,
               'shipping' => $shippingAmount,
               'total' => $totalAmount,
               'display' => [
                   'subTotal' => $subTotal->formatted(),
                   'taxAmount' => $taxAmount->formatted(),
                   'discount' => Money::format(0),
                   'shipping' => $shippingAmount->formatted(),
                   'total' => $totalAmount->formatted(),
               ],
           ],
           'reward' => [
               'points' => $rewardPoints,
               'value' => Money::format($rewardPoints * 0.5)
           ],
           'items' => $bag,
           'instance' => $this
       ];
    }



}

