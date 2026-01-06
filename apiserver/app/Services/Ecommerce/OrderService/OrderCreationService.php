<?php

namespace App\Services\Ecommerce\OrderService;

use App\Casts\OrderStatusCast;
use App\Casts\PaymentMethodCast;
use App\Models\Ecommerce\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Affiliate\CommissionProcessorService;
use App\Services\Ecommerce\CartService\CartService;
use App\Services\UserServices\UserWalletService;
use Illuminate\Support\Facades\DB;

class OrderCreationService
{

    protected CartService $cartService;
    protected ?CommissionProcessorService $commissionProcessor = null;

    protected UserWalletService $walletService;

    protected Order $order;
    protected Transaction $transaction;


    public function __construct(CartService $cartService,?CommissionProcessorService $commissionProcessor = null
    ) {
        $this->cartService = $cartService;
        $this->commissionProcessor = $commissionProcessor;
        // Init Wallet Service
        $this->walletService = new UserWalletService();
    }

    public static function make(CartService $cartService,?CommissionProcessorService $commissionProcessor = null): static
    {
        return new static($cartService,$commissionProcessor);
    }

    public function getOrder():?Order
    {
        return $this->order;
    }

    public function getTransaction():?Transaction
    {
        return $this->transaction;
    }

    public function place($user, $cartTotal, $paymentMethod, $request, $cartService,$shippingAddress,$billingAddress)
    {


    }

    public function createOrder($user, $cartTotal,$shippingAddress,$billingAddress)
    {
        $this->order = Order::create([
            'customerable_type' => get_class($user),
            'customerable_id' => $user->id,
            'status' => OrderStatusCast::PENDING,
            'subtotal' => $cartTotal['subtotal'],
            'tax' => $cartTotal['tax'],
            'shipping_cost' => $cartTotal['shipping_cost'],
            'discount' => $cartTotal['discount'],
            'total' => $cartTotal['total'],
            'total_bv' => $cartTotal['bv'],
            'total_pv' => $cartTotal['pv'],
            'total_reward_points' => $cartTotal['reward_points'],
            'shipping_address_id' => $shippingAddress->id,
            'billing_address_id' => $billingAddress->id,
            'quantity' => $cartTotal['total_quantity'],
        ]);

        return $this;
    }

    public function createOrderItem(array $item)
    {
        return $this->order->items()->create([
            'product_id' => $item['product_id'],
            'quantity' => $item['allocated_quantity'],
            'unit_price' => $item['unit_price'],
            'tax' => $item['item_tax'],
            'total_price' => $item['item_total'],
            'bv' => $item['bv'],
            'pv' => $item['pv'],
            'reward_points' => $item['reward_points'],
            'metadata' => [
                'stock_allocations' => $item['stock_entries'],
            ],
        ]);
    }

    public function payWithWallet(User $user)
    {

        // Init Wallet Service
        $wallet = $this->walletService->getOrCreateWallet($user);

        if (! $wallet->hasSufficientBalance($this->order->total)) {
            throw new \Exception('Insufficient wallet balance');
        }

        // Debit wallet
        $this->transaction = $this->walletService->debit(
            $wallet,
            $this->order->total,
            'order_payment',
            "Payment for Order #{$this->order->order_number}"
        );

        // Update order
        $this->order->update([
            'status' => OrderStatusCast::CONFIRMED,
            'payment_success' => true,
        ]);

        // Link transaction
        $this->transaction->update([
            'transactionable_type' => get_class($this->order),
            'transactionable_id' => $this->order->id,
        ]);

    }

    public function payWithOnline($user,$paymentMethod): void
    {
       $this->transaction =  $this->order->createDebitTransaction(
           customer: $user,
           paymentMethod: $paymentMethod,
           redirectSuccessUrl: config('app.client_url')."/order/{$this->order->uuid}?payment=success",
           redirectFailureUrl: config('app.client_url')."/order/{$this->order->uuid}?payment=failed",
           wallet: $this->walletService->getOrCreateWallet($user),
           purpose: "Payment for Order #{$this->order->order_number}"
       );
    }


}
