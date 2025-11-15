<?php

namespace App\Services\OrderService;

use App\Models\Localization\Address;
use App\Models\Store\Order\Order;
use App\Models\Wallet\Payment;
use App\Services\CartService\CartService;
use App\Services\CheckoutService\CheckoutService;
use Illuminate\Database\Eloquent\Model;

class ProductOrderManagerService extends CheckoutService
{

    protected array $cartMeta;
    protected ?CartService $cartService = null;
    protected ?Model $customer = null;
    protected Address $billingAddress;
    protected Address $shippingAddress;
    protected Order $order;
    protected Payment $payment;



    public static function make():static
    {
        return new static();
    }

    public function setCustomer(Model $customer): static
    {
        $this->customer = $customer;
        $this->cartService = CartService::make($this->customer);
        $this->setCartMeta($this->cartService->getMeta());
        return $this;
    }


    public function setCartMeta(array $cartMeta):static
    {
        $this->cartMeta = $cartMeta;
        $this->cartService = $this->cartService ?? $this->cartMeta['instance'];
        $this->customer = $this->customer ?? $this->cartService->getCustomer();
        $this->billingAddress = $this->customer->home_address;
        return $this;
    }


    public function setShippingAddress(Address $shippingAddress):static
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }



    public function checkout(bool $viaWallet = false)
    {
        dd($this);
        $this->order = $this->createOrder();
    }



    public function confirm(Order $order):bool
    {

    }



    // Order Place

    protected function createOrder():order
    {

    }



    // Order Confirm



}
