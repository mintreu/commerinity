<?php

namespace App\Services\OrderService\ProductHandler;

use App\Filament\Resources\Store\Order\OrderResource;
use App\Models\Enums\Shop\OrderStatusCast;
use App\Models\Localization\Address;
use App\Models\Store\Order\Order;
use App\Models\Wallet\Payment;
use App\Services\CartService\CartService;
use App\Services\CheckoutService\CheckoutService;
use App\Services\MoneyService\Money;
use App\Services\ProviderServices\PaymentService\PaymentService;
use Illuminate\Database\Eloquent\Model;

class ProductOrderCreationService extends CheckoutService
{

    protected array $items;
    protected Address $shippingAddress;
    protected Address $billingAddress;
    protected Order $order;
    protected array $cartMeta = [];
    protected CartService $cartService;
    protected Model $customer;
    protected Payment $payment;



    public function items(array $cartProducts): static
    {
        $this->items = $cartProducts;
        return $this;
    }


    public function cartMeta(array $cartMeta):static
    {
        $this->cartMeta = $cartMeta;
        $this->cartService = $this->cartMeta['instance'];
        $this->customer = $this->cartService->getCustomer();
        return $this;
    }

    public function shippingAddress(Address $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }

    public function billingAddress(Address $billingAddress): static
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

    public function placeOrder():?Order
    {
        $subTotal = new Money(0);
        $discount = new Money(0);
        $tax = new Money(0);
        $shipping = new Money(0);
        $total = new Money(0);
        foreach ($this->items as $cartItem)
        {
            $subTotal->add($cartItem['totals']['subTotal']);
            $discount->add($cartItem['totals']['discount']);
            $tax->add($cartItem['totals']['taxAmount']);
            $shipping->add($cartItem['totals']['shipping']);
            $total->add($cartItem['totals']['total']);
        }

        // Create Order
        $this->order = $this->createOrder($subTotal,$discount,$tax,$shipping,$total);
        $this->payment = $this->getInitPayment($this->order,OrderResource::getUrl('view',['record' => $this->order->uuid]));
        $this->attachProductsToOrderProducts();

        $this->cartService->empty();
        // Redirect to Checkout
        return $this->generateProviderOrder();
    }






    protected function createOrder($subTotal,$discount,$tax,$shipping,$total):Order
    {

        return $this->customer->orders()->create([
            'voucher' => $this->cartMeta['coupon']['code'],
            'quantity' => $this->cartMeta['totals']['quantity'],
            'amount' => $total->getValue(),
            'subtotal' => $subTotal->getValue(),
            'discount' => $discount->getValue(),
            'tax' => $tax->getValue(),
            'total' => $total->getValue(),
            'status' => OrderStatusCast::PENDING,
            'payment_success' => false,
            'expire_at' => now()->addDay(),
            'customer_gstin' => null, // need data here
            'shipping_is_billing' => $this->shippingAddress->id == $this->billingAddress->id,
            'billing_address_id' => $this->billingAddress->id,
            'shipping_address_id' => $this->shippingAddress->id,
            'is_cod' => false,
        ]);
    }




    private function attachProductsToOrderProducts()
    {

        foreach ($this->items as $item)
        {
            $this->order->orderProducts()->create([
                'quantity' => $item['totals']['quantity'],
                'amount' => $item['totals']['subTotal']->getValue(),
                'discount' => $item['totals']['discount']->getValue(),
                'tax' => $item['totals']['taxAmount']->getValue(),
                'total' => $item['totals']['total']->getValue(),
                'product_id' => $item['item']->id,
            ]);
        }

    }

    private function generateProviderOrder()
    {

        $address = $this->billingAddress;
        $address->load(['state','country']);



        $productInfo = collect($this->items)->map(function ($item) {
            return "{$item['totals']['quantity']}x {$item['item']->name}";
        })->implode(', ');

        // Example output: "2x T-Shirt, 1x Jeans, 3x Sneakers"

        return PaymentService::make()->order()->create([
            'txnid' => $this->payment->uuid,
            'amount' => $this->payment->amount,
            'firstname' => $this->customer->name,
            'email' => $this->customer->email,
            'phone' => $this->customer->mobile,
            'productinfo' => $productInfo,
            'surl' => route('order.confirm',['payment' => $this->payment->uuid]),
            'furl' => route('order.retry',['payment' => $this->payment->uuid]),
            'address1' => $address->address_1,
            'city' => $address->city,
            'state' => $address->state->name,
            'country' => $address->country->name,
            'zipcode' => $address->postal_code,
        ]);

    }




}
