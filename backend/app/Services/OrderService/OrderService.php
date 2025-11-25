<?php

namespace App\Services\OrderService;

use App\Casts\OrderStatusCast;
use App\Models\Order\Order;
use App\Models\Order\OrderInvoice;
use App\Models\Order\OrderProduct;
use App\Models\Order\OrderShipment;
use App\Models\User;
use App\Notifications\Order\OrderNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mintreu\LaravelCommerinity\Services\CartService\Cart;
use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelIntegration\Casts\IntegrationTypeCast;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Mintreu\LaravelIntegration\Models\Integration;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelTransaction\Casts\TransactionStatusCast;
use Mintreu\LaravelTransaction\Models\Transaction;
use Mintreu\LaravelTransaction\Services\WalletService\WalletService;

class OrderService
{

    protected ?Order $order = null;
    protected ?array $meta = [];
    protected null|Model|User $customer = null;
    protected ?Address $billingAddress = null;
    protected ?Address $shippingAddress = null;
    protected ?string $provider = null;
    protected ?string $error = null;
    protected ?Transaction $transaction = null;
    protected ?string $successUrl = null;

    public static function make()
    {
        return new static();
    }

    protected function setError(string $error): void
    {
        $this->error = $error;
    }

    public function getError():?string
    {
        return $this->error;
    }


    public function place(Cart $cart,Address $billing, Address $shipping,string $provider): array
    {
        $allAvailablePaymentProviders = LaravelIntegration::make()->getAvailableProviders(IntegrationTypeCast::PAYMENT->value);
        $defaultOnlinePayment = collect($allAvailablePaymentProviders)->where('default',true)->first();

        $this->provider = match ($provider) {
                'wallet-payment', 'wallet' =>  $allAvailablePaymentProviders['wallet-payment']['slug'],
                'online','online-payment', 'razorpay', 'razorpay-payment', 'cash-free', 'cash-free-payment' =>  $defaultOnlinePayment['slug'],
                'cash', 'cod','cod-payment', 'cash-on-delivery' =>  $allAvailablePaymentProviders['cash-payment']['slug'],
                default => throw new \InvalidArgumentException("Unsupported payment provider: {$this->provider}"),
            };





        // Create Order & Attach Product into Order Items
        $this->order = $this->create(cart: $cart,billing: $billing,shipping: $shipping,isFilament: false);


        if ($this->order)
        {
            // Create Transaction
            $this->transaction = $this->resolveTransaction();
        }


        // Cleanup Cart For Next Use
        $cart->empty();
        return [
            'success' => $this->getError() === null,
            'errors' =>  $this->error,
            'redirect' => $this->transaction ? route('checkout',['transaction' => $this->transaction?->uuid]) : null,
        ];
    }


    public function create(Cart $cart,Address $billing, Address $shipping,bool $isFilament = false,?string $filamentResource = null):?Order
    {
        $this->meta = $cart->getMeta();
        $this->customer = $cart->getCustomer();
        $this->billingAddress = $billing;
        $this->shippingAddress = $shipping;

        if (($this->meta['summary']['quantity']) == null || $this->meta['summary']['quantity'] == 0)
        {
            $this->setError('no product found for order');
            return null;
        }



        $this->order = $this->customer->orders()->create([
            'voucher' => $this->meta['summary']['coupon_code'],
            'quantity' => $this->meta['summary']['quantity'],

            'subtotal' => $this->meta['summary']['sub_total']->getAmount(),
            'discount' => $this->meta['summary']['discount']->getAmount(),
            'tax' => $this->meta['summary']['tax']->getAmount(),
            'shipping_cost' => $this->meta['summary']['shipping_cost']->getAmount(),
            'total' => $this->meta['summary']['total']->getAmount(),
            'status' => OrderStatusCast::PENDING,
            'payment_success' => false,
            'expire_at' => now()->addDay(),
            'customer_gstin' => null, // need data here
            'billing_address_id' => $this->billingAddress->id,
            'shipping_address_id' => $this->shippingAddress->id,
        ]);

        // Prepare Success Url
        $this->successUrl = $isFilament ? $filamentResource::getUrl('view',['record' => $this->order->uuid]) : config('app.client_url').'order/'.$this->order->uuid;

        // Attached Products into Order Items
        foreach ($this->meta['items'] as $item)
        {
            $this->order->orderProducts()->create([
                'quantity' => $item['summary']['quantity'],
                'amount' => $item['summary']['raw']['sub_total']->getAmount(),
                'discount' => $item['summary']['raw']['discount']->getAmount(),
                'tax' => $item['summary']['raw']['tax']->getAmount(),
                'total' => $item['summary']['raw']['total']->getAmount(),
                'product_id' => $item['product_id'],
            ]);

        }


        return  $this->order;
    }



    public function payIt(Model|Order $order, ?string $provider, bool $hasResource = false, ?string $resource = null, bool $forced = false): array
    {
        $this->provider = $provider;
        $this->order = $order;

        $this->order->load(['transaction','customerable']);


        $this->customer = $this->order->customerable;

        $existingTransaction = $this->order->transaction ?? null;

        // If already paid
        if ($existingTransaction?->isPaid()) {
            $this->setError('Order already paid!');
        }

        // If exists and not verified
        if ($existingTransaction && !$existingTransaction->isVerified()) {
            $this->successUrl = $existingTransaction->successUrl();

            $this->transaction = $existingTransaction;

            // If expired & unpaid
            if ($this->transaction->isExpired() && !$this->transaction->isPaid()) {

                if (!$forced) {
                    $this->setError(
                        'An existing expired transaction already exists. To reset it, enable the reset option.'
                    );
                } else {
                    // Reset by deleting old and creating new
                    $this->transaction->delete();
                    $this->transaction = $this->resolveTransaction();
                }
            }
        }

        // If no transaction exists, create one
        if (!$existingTransaction) {
            $this->successUrl = $hasResource ? $resource::getUrl('view',['record' => $this->order->uuid]) : config('app.client_url').'order/'.$this->order->uuid;
            $this->transaction = $this->resolveTransaction();
        }

        return [
            'success' => $this->getError() === null,
            'errors'   => $this->error,
            'redirect' => $this->transaction ? route('checkout', ['transaction' => $this->transaction?->uuid]) : null,
        ];
    }





    protected function resolveTransaction(): ?Transaction
    {

        if ($this->provider === 'online') {
            $integration = Integration::payment()
                ->where('default', true)
                ->first();

            $this->provider = $integration->url;
        }


        $this->transaction =  match ($this->provider) {
            'wallet-payment', 'wallet' =>  $this->payWithWallet(),
            'online','online-payment', 'razorpay', 'razorpay-payment', 'cash-free', 'cash-free-payment' =>  $this->payWithOnlineGateway(),
            'cash', 'cod','cod-payment', 'cash-on-delivery' =>  $this->payWithCOD(),
            default => throw new \InvalidArgumentException("Unsupported payment provider: {$this->provider}"),
        };


        return $this->transaction;

    }

    private function payWithWallet():?Transaction
    {
        $userWallet = $this->customer?->wallet;
        if ($userWallet)
        {
            if (LaravelMoney::make($userWallet?->balance)->greaterThanOrEqual($this->order->amount))
            {
                // here

                return WalletService::make($userWallet)->payFor(
                    payable_record: $this->order,
                    successUrl: $this->successUrl,
                    failureUrl: $this->successUrl,
                    amount_column: 'total',
                    purpose: 'Purchasing Products'
                )->getTransaction();

            }else{
                $this->setError('Insufficient balance in customer wallet');
            }
        }else{
            $this->setError('Customer have no wallet account');
        }
        return null;

    }

    private function payWithOnlineGateway():?Transaction
    {
        return $this->order->createDebitTransaction(
            customer: $this->customer,
            redirect_success_url: $this->successUrl,
            redirect_failure_url: $this->successUrl,
            wallet: $this->customer?->wallet,
            purpose: 'Purchasing Products',
            paymentProviderSlug: $this->provider,
            expireAfterMinutes: 60,
            amount: 'total'
        );
    }

    private function payWithCOD():?Transaction
    {
        return null;
    }




    /**
     * Validation Of Order
     */
    public function validate(Order $order,Transaction $transaction)
    {
        $this->order = $order;
        if (is_null($transaction))
        {
            $this->order->loadMissing(['transaction','orderProducts','customer']);
        }
        $this->transaction = $transaction ?? $this->order->transaction;
        $this->customer = $this->order->customer;
        $isPaid = $this->transaction->verified && $this->transaction->status->value == TransactionStatusCast::COMPLETED->value;


        if ($isPaid)
        {
            $this->processOrderConfirmation();

            $this->order->update([
                'status'     => OrderStatusCast::CONFIRM
            ]);
        }

        if ($this->customer?->email)
        {
            $this->customer->notify(new OrderNotification($this->order));
        }




    }






    /**
     * Process Order
     * Check Stock
     * Send For Shipment
     * Make Invoice
     * Full Process Related Products
     * @return void
     */
    private function processOrderConfirmation(): void
    {

        $this->order->orderProducts->each(function (OrderProduct $orderProduct) {

            // Update Sold Stock Of Each Ordered Products
            $allowedOrderedProducts = $this->getQualifiedUpdatedOrderedProductStockArray($orderProduct);
            if (empty($allowedOrderedProducts))
            {
                $this->setError('no stock available for '.$orderProduct->product->name);
            }else{
                $groupedStock = collect($allowedOrderedProducts)->groupBy('pickup_address_id');

                foreach ($groupedStock as $pickupAddress_id => $data) {
                    $totalQuantityOfThisPickupAddress = $data->sum(function ($item) {
                        return $item['quantity'];
                    });

                    // Step 1.2
                    $newOrderShipment = $this->makeOrderShipment($orderProduct, $totalQuantityOfThisPickupAddress, $pickupAddress_id);
                    if (!$newOrderShipment)
                    {
                        Log::error('shipment not generate for order '.$this->order->uuid);
                    }
                    // Step 1.3
                    $newOrderInvoice = $this->makeOrderInvoice($newOrderShipment, $orderProduct);
                    if (!$newOrderInvoice)
                    {
                        Log::error('invoice not generate for order '.$this->order->uuid);
                    }
                }
            }

        });



    }

    private function getQualifiedUpdatedOrderedProductStockArray(OrderProduct $orderProduct): array
    {
        $product = $orderProduct->product;
        $requiredQuantity = $orderProduct->quantity;
        $quantityFulfilled = 0;
        $bag = [];

        if (!$product->tiers)
        {
            $product->loadMissing('tiers');
        }

        foreach ($product->tiers as $productStock) {
            if ($productStock->in_stock_quantity >= $requiredQuantity - $quantityFulfilled) {
                // Deducted Stock Quantity & Update Product Stock
                $quantityToDeduct = $requiredQuantity - $quantityFulfilled;

                $bag[] = [
                    'quantity' => $quantityToDeduct,
                    'stock' => $productStock,
                    'pickup_address_id' => $productStock->addresses->first()->id,
                ];
                // Update the quantity fulfilled
                $quantityFulfilled += $quantityToDeduct;
                // Break the loop since the required quantity is fulfilled
                break;
            }
        }

        // If Fulfil Order Quantity, Then Stock Will Be Updated
        if ($quantityFulfilled === $requiredQuantity) {

            foreach ($bag as $data) {
                $data['stock']->sold_quantity += $data['quantity'];
                $data['stock']->save();
            }
        } else {
            $this->setError($product->name.' out of stock!');
        }
        return $bag;
    }







    protected function makeOrderShipment(OrderProduct $orderProduct,int $allowedQuantity,int $pickup_address_id)
    {
        return $orderProduct->shipment()->create([
            'order_id' => $this->order->id,
            'total_quantity' => $allowedQuantity,
            'pickup_address' => $pickup_address_id,
            'delivery_address' => $this->order->shipping_address_id,
//            'cod' => $this->order->is_cod,
            'status' => OrderShipment::PROCESSING,
        ]);
    }



    protected function makeOrderInvoice(OrderShipment $orderShipment, OrderProduct $orderProduct): OrderInvoice
    {
        $newInvoice = $orderShipment->invoice()->create([
            'uuid' => 'INV_'.$this->order->uuid,
            'order_id' => $this->order->id,
            'order_product_id' => $orderProduct->id,
        ]);
        $orderShipment->invoice_uid = $newInvoice->uuid;
        $orderShipment->save();

        return $newInvoice;
    }







}
