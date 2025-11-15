<?php

namespace App\Services\OrderService;

use App\Models\Localization\Address;
use App\Models\Store\Order\Order;
use App\Models\Wallet\Payment;
use App\Services\CartService\CartService;
use App\Services\OrderService\ProductHandler\ProductOrderConfirmService;
use App\Services\OrderService\ProductHandler\ProductOrderCreationService;
use App\Services\ProviderServices\PaymentService\PaymentService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class OrderService
{

    protected CartService $cartService;
    protected array $cartMeta;
    protected Model $customer;
    protected Address $shippingAddress;
    protected Address $billingAddress;




    public static function make():static
    {
        return new static();
    }

    public function cartMeta(array $cartMeta): static
    {
        $this->cartMeta = $cartMeta;
        $this->cartService = $this->cartMeta['instance'];
        $this->customer = $this->cartService->getCustomer();
        $this->billingAddress = $this->customer->home_address;
        return $this;
    }



    public function shippingAddress(Address $shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }

    public function checkout()
    {
        $allItems = collect($this->cartMeta['items']);
        $productItems = $allItems->where('type','Product');
        $stageItems = $allItems->where('type','Stage');

        // Bulk Order Checkout
//        foreach ($allItems->groupBy('type')->toArray() as $type => $items)
//        {
//            if ($type == 'Product')
//            {
//            }
//            Notification::make()->title('Process Not Complete')->body('Only Products Unlocked!')->warning()->send();
//        }


        // Single Only Product Checkout
        $cartProducts = $allItems->where('type','=','Product')->toArray();
        if ($cartProducts)
        {
            return ProductOrderCreationService::make()
                ->items($cartProducts)
                ->cartMeta($this->cartMeta)
                ->shippingAddress($this->shippingAddress)
                ->billingAddress($this->billingAddress)
                ->placeOrder();
        }




    }

    public function confirmOrder(Order $order):bool
    {
        $confirmService = ProductOrderConfirmService::make($order);
        return $confirmService->confirm();
    }


}
