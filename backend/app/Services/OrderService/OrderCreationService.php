<?php

namespace App\Services\OrderService;

use App\Casts\OrderStatusCast;
use App\Models\Order\Order;
use App\Services\CartService\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelIntegration\Support\ProviderOrder;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Models\Transaction;

class OrderCreationService
{

    protected ?Model $customer = null;
    protected Cart $cart;
    protected array $cartMeta;
    protected Address $shippingAddress;
    protected Address $billingAddress;
    protected bool $asGift = false;
    protected ?string $provider = null;

    protected Order $order;
    protected ?string $error = null;
    protected ?Transaction $transaction = null;

    /**
     * @param Model|null $customer
     */
    public function __construct(?Model $customer = null)
    {
        $this->customer = $customer;
    }


    public static function make(?Model $customer = null): static
    {
        return new static($customer);
    }

    protected function setError(string $error): void
    {
        $this->error = $error;
    }

    public function getError():?string
    {
        return $this->error;
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


    protected function initilizeCart(Request $request)
    {

        $this->cart = new Cart($this->customer);
        $this->cart->capture($request);
        $this->cartMeta = $this->cart->getMeta(false);
    }




    public function placeOrder(Request $request): static
    {
        $this->initilizeCart($request);
        if ($this->cartMeta['summary']['quantity'])
        {
            $this->order = $this->createOrder();

            if ($this->order)
            {
                if ($this->order->transaction()->count())
                {
                    $this->order->load('transaction');
                    $this->transaction = $this->order->transaction;
                }else{
                    $this->createOrderPayment();
                }


            }
        }else{
            $this->setError('no product found for order');
        }



        $this->attachProductsToOrderProducts();

        $this->cart->empty();

        return $this;
    }


    public function getCheckoutUrl():?string
    {

        if(!is_null($this->transaction))
        {
            return route('checkout',['transaction' => $this->transaction->uuid]);
        }
        return null;
    }


    // Process Order Creation

    protected function createOrder():Order
    {
        $customer = $this->customer ? [
            'name'      => $this->customer->name,
            'email'     => $this->customer->email,
            'mobile'    => $this->customer->mobile
        ] : [
            'name'      => $this->billingAddress->person_name,
            'email'     => $this->billingAddress->person_email,
            'mobile'    => $this->billingAddress->person_mobile
        ];



        $orderFillables = [
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
            'is_cod' => $this->provider === 'cash-payment',
            'has_guest' => is_null($this->customer),
            'customer_name' => $customer['name'],
            'customer_email'    => $customer['email'],
            'customer_mobile'   => $customer['mobile'],
        ];

        return !is_null($this->customer) ? $this->customer->orders()->create($orderFillables) : Order::create($orderFillables);
    }



    protected function createOrderPayment()
    {
        $customer = [
            'name'      => $this->order->customer_name,
            'email'     => $this->order->customer_email,
            'mobile'    => $this->order->customer_mobile
        ];

        $successUrl = $this->customer ? config('app.client_url').'dashboard/orders/'.$this->order->uuid : config('app.client_url').'order/'.$this->order->uuid;
        $failureUrl = $this->customer ? config('app.client_url').'dashboard/orders/'.$this->order->uuid : config('app.client_url').'order/'.$this->order->uuid ;

        $this->transaction = $this->order->createDebitTransaction(
            customer: $customer,
            redirect_success_url: $successUrl,
            redirect_failure_url: $failureUrl,
            wallet: $this->customer?->wallet,
            purpose: 'Purchasing Products',
            paymentProviderSlug: $this->provider,
            expireAfterMinutes: 60
        );

    }




    private function attachProductsToOrderProducts()
    {

        foreach ($this->cartMeta['items'] as $item)
        {
            $this->order->orderProducts()->create([
                'quantity' => $item['summary']['quantity'],
                'amount' => $item['summary']['sub_total']->getValue(),
                'discount' => $item['summary']['discount']->getValue(),
                'tax' => $item['summary']['tax']->getValue(),
                'total' => $item['summary']['total']->getValue(),
                'product_id' => $item['product_id'],
            ]);
        }

    }

}
