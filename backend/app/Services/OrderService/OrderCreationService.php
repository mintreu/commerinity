<?php

namespace App\Services\OrderService;

use App\Casts\OrderStatusCast;
use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Mintreu\LaravelCommerinity\Services\CartService\Cart;
use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Models\Transaction;
use Mintreu\LaravelTransaction\Services\WalletService\WalletService;

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
    protected ?string $checkoutUrl = null;

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
            $this->order->load('transaction');
            if ($this->order)
            {
                if ((bool) $this->order?->transaction?->count())
                {
                    $this->transaction = $this->order->transaction;
                }else{
                    $this->createOrderTransaction();
                }
            }
        }else{
            $this->setError('no product found for order');
        }


        return $this;
    }


    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }


    public function getCheckoutUrl():?string
    {
       return $this->checkoutUrl;
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
            'amount' => $this->cartMeta['summary']['total']->getAmount(),
            'subtotal' => $this->cartMeta['summary']['sub_total']->getAmount(),
            'discount' => $this->cartMeta['summary']['discount']->getAmount(),
            'tax' => $this->cartMeta['summary']['tax']->getAmount(),
            'total' => $this->cartMeta['summary']['total']->getAmount(),
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



    protected function createOrderTransaction()
    {



        // Customer Info
        $customer = [
            'name'      => $this->order->customer_name,
            'email'     => $this->order->customer_email,
            'mobile'    => $this->order->customer_mobile
        ];
        // Redirects
        $successUrl = $this->customer ? config('app.client_url').'/dashboard/orders/'.$this->order->uuid : config('app.client_url').'order/'.$this->order->uuid;
        $failureUrl = $this->customer ? config('app.client_url').'/dashboard/orders/'.$this->order->uuid : config('app.client_url').'order/'.$this->order->uuid ;



        // Check given provider
        $userWallet = $this->customer?->wallet;
        if ($this->provider == 'wallet-payment' && $userWallet)
        {

            if (LaravelMoney::make($userWallet?->balance)->greaterThanOrEqual($this->order->amount))
            {
                $this->transaction = WalletService::make($userWallet)->payFor(
                    payable_record: $this->order,
                    successUrl: $successUrl,
                    failureUrl: $failureUrl,
                    amount_column: 'amount',
                    purpose: 'Purchasing Products'
                )->getTransaction();

                $this->checkoutUrl =  $successUrl;
                // For Wallet Case Transaction always Confirm, no need to checkout again
                $this->processLeftJobs();
                // Confirm This Order
                OrderConfirmService::make($this->order,$this->transaction)->confirm();
            }else{
                $this->checkoutUrl =  $failureUrl;
                $this->setError('insufficient balance in your wallet!');
            }



        }else{

            // Other Providers
            $this->transaction = $this->order->createDebitTransaction(
                customer: $customer,
                redirect_success_url: $successUrl,
                redirect_failure_url: $failureUrl,
                wallet: $this->customer?->wallet,
                purpose: 'Purchasing Products',
                paymentProviderSlug: $this->provider,
                expireAfterMinutes: 60
            );

            $this->checkoutUrl = route('checkout',['transaction' => $this->transaction->uuid]);
            // Left Jobs
            $this->processLeftJobs();
            // Confirmation will apply after payment
        }




    }


    protected function processLeftJobs()
    {


        $this->attachProductsToOrderProducts();


        $this->cart->empty();
    }


    private function attachProductsToOrderProducts()
    {

        foreach ($this->cartMeta['items'] as $item)
        {

            $newOrderProduct = $this->order->orderProducts()->create([
                'quantity' => $item['summary']['quantity'],
                'amount' => $item['summary']['raw']['sub_total']->getAmount(),
                'discount' => $item['summary']['raw']['discount']->getAmount(),
                'tax' => $item['summary']['raw']['tax']->getAmount(),
                'total' => $item['summary']['raw']['total']->getAmount(),
                'product_id' => $item['product_id'],
            ]);

        }

    }

}
