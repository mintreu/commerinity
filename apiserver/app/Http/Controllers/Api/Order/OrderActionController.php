<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Order;

use App\Casts\OrderStatusCast;
use App\Casts\PaymentMethodCast;
use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use App\Services\Ecommerce\CartService\CartService;
use App\Services\UserServices\UserWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class OrderActionController extends Controller
{
    public function __construct(
        private readonly UserWalletService $walletService,
    ) {}

    /**
     * Checkout from cart and create order
     *
     * POST /api/cart/checkout
     */
    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user();
        $cartService = new CartService($user);
        $cartService->capture($request);

        if ($cartService->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty',
            ], 400);
        }

        $request->validate([
            'payment_method' => ['required', 'string', 'in:wallet,cashfree,razorpay'],
            'shipping_address_id' => ['required', 'integer', 'exists:addresses,id'],
            'billing_address_id' => ['nullable', 'integer', 'exists:addresses,id'],
        ]);

        $shippingAddress = $user->addresses()->find($request->shipping_address_id);
        $validation = $cartService->validate($shippingAddress);

        if (! $validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Cart validation failed',
                'errors' => $validation['errors'],
            ], 422);
        }

        $cartTotal = $validation['cart_total'];
        $paymentMethod = PaymentMethodCast::from($request->payment_method);

        try {
            return DB::transaction(function () use ($user, $cartTotal, $paymentMethod, $request, $cartService) {
                // 1. Create Order
                $order = Order::create([
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
                    'shipping_address_id' => $request->shipping_address_id,
                    'billing_address_id' => $request->billing_address_id ?? $request->shipping_address_id,
                    'quantity' => $cartTotal['total_quantity'],
                ]);

                // 2. Add Order Items
                foreach ($cartTotal['items'] as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['allocated_quantity'],
                        'unit_price' => $item['unit_price'],
                        'tax' => $item['item_tax'],
                        'subtotal' => $item['item_total'],
                        'bv' => $item['bv'],
                        'pv' => $item['pv'],
                        'reward_points' => $item['reward_points'],
                        'metadata' => [
                            'stock_allocations' => $item['stock_entries'],
                        ],
                    ]);
                }

                // 3. Clear Cart
                $cartService->clear();

                // 4. Handle Wallet Payment directly if chosen
                if ($paymentMethod === PaymentMethodCast::WALLET) {
                    $wallet = $this->walletService->getOrCreateWallet($user);

                    if (! $wallet->hasSufficientBalance($order->total)) {
                        throw new \Exception('Insufficient wallet balance');
                    }

                    // Debit wallet
                    $transaction = $this->walletService->debit(
                        $wallet,
                        $order->total,
                        'order_payment',
                        "Payment for Order #{$order->order_number}"
                    );

                    // Update order
                    $order->update([
                        'status' => OrderStatusCast::CONFIRMED,
                        'payment_success' => true,
                    ]);

                    // Link transaction
                    $transaction->update([
                        'transactionable_type' => get_class($order),
                        'transactionable_id' => $order->id,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Order placed and paid successfully via wallet',
                        'data' => [
                            'order_uuid' => $order->uuid,
                            'order_number' => $order->order_number,
                            'status' => $order->status->value,
                        ]
                    ], 201);
                }

                // 5. External Payment (Cashfree/Razorpay)
                $transaction = $order->createDebitTransaction(
                    customer: $user,
                    paymentMethod: $paymentMethod,
                    redirectSuccessUrl: config('app.client_url')."/orders/{$order->uuid}?payment=success",
                    redirectFailureUrl: config('app.client_url')."/orders/{$order->uuid}?payment=failed",
                    wallet: $this->walletService->getOrCreateWallet($user),
                    purpose: "Payment for Order #{$order->order_number}"
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Order placed. Redirecting to payment...',
                    'data' => [
                        'order_uuid' => $order->uuid,
                        'order_number' => $order->order_number,
                        'checkout_url' => route('checkout.show', ['transaction' => $transaction->uuid]),
                        'transaction_uuid' => $transaction->uuid,
                    ]
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage(),
            ], 500);
        }
    }
}
