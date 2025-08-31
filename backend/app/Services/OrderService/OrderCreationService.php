<?php

namespace App\Services\OrderService;

use App\Casts\OrderStatusCast;
use App\Models\Order\Order;
use App\Services\CartService\Cart;
use Illuminate\Database\Eloquent\Model;
use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelIntegration\Support\ProviderOrder;
use Mintreu\LaravelMoney\LaravelMoney;

class OrderCreationService
{

    protected Model $customer;
    protected Cart $cart;
    protected array $cartMeta;
    protected Address $shippingAddress;
    protected Address $billingAddress;
    protected bool $asGift = false;
    protected ?string $provider = null;

    protected Order $order;



    /**
     * @param Model $customer
     */
    public function __construct(Model $customer)
    {
        $this->customer = $customer;
        $this->cart = new Cart($this->customer);
        $this->cartMeta = $this->cart->getMeta(false);
    }


    public static function make(Model $customer): static
    {
        return new static($customer);
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

    public function paymentProvider(string $payment_provider): static
    {
        $this->provider = $payment_provider;
        return $this;
    }

    public function asGift(bool $gift = false)
    {
        $this->asGift = $gift;
        return $this;
    }

    public function placeOrder(): static
    {
        $this->order = $this->createOrder();
        if ($this->order)
        {
            $this->createOrderPayment();
        }
//
//        $this->attachProductsToOrderProducts();
//
//        $this->cart->empty();

        return $this;
    }


    public function getCheckoutUrl()
    {

        dd($this,LaravelIntegration::payment($this->provider));
    }


    // Process Order Creation

    protected function createOrder():Order
    {

        return $this->customer->orders()->create([
            'voucher' => $this->cartMeta['summary']['coupon_code'],
            'quantity' => $this->cartMeta['summary']['quantity'],
            'amount' => $this->cartMeta['summary']['total']->getValue(),
            'subtotal' => $this->cartMeta['summary']['sub_total']->getValue(),
            'discount' => $this->cartMeta['summary']['discount']->getValue(),
            'tax' => $this->cartMeta['summary']['tax']->getValue(),
            'total' => $this->cartMeta['summary']['total']->getValue(),
            'status' => OrderStatusCast::PENDING,
            'payment_success' => false,
            'expire_at' => now()->addDay(),
            'customer_gstin' => null, // need data here
            'shipping_is_billing' => $this->shippingAddress->id == $this->billingAddress->id,
            'billing_address_id' => $this->billingAddress->id,
            'shipping_address_id' => $this->shippingAddress->id,
            'is_cod' => $this->provider === 'cod-payment',
        ]);
    }



    protected function createOrderPayment()
    {
        $providerOrder = LaravelIntegration::payment()->order()->create(function (ProviderOrder $providerOrder){
            $providerOrder
                ->customer($this->customer)
                ->amount($this->order->amount)
                ->currency(LaravelMoney::defaultCurrency())
                ->expireAfterDays(20)
                ->receipt($this->order->uuid);
        });

        dd($providerOrder);

        $this->order->transaction()->create([]);
    }




    private function attachProductsToOrderProducts()
    {

        foreach ($this->cartMeta['items'] as $item)
        {
            $this->order->orderProducts()->create([
                'quantity' => $item['summary']['quantity'],
                'amount' => $item['summary']['subTotal']->getValue(),
                'discount' => $item['summary']['discount']->getValue(),
                'tax' => $item['summary']['taxAmount']->getValue(),
                'total' => $item['summary']['total']->getValue(),
                'product_id' => $item['product_id'],
            ]);
        }

    }

}
