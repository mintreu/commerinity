<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Order;

use App\Casts\OrderStatusCast;
use App\Casts\PaymentMethodCast;
use App\Http\Controllers\Controller;
use App\Models\Ecommerce\Order;
use App\Services\Ecommerce\CartService\CartService;
use App\Services\Ecommerce\OrderService\OrderCreationService;
use App\Services\UserServices\UserWalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class OrderActionController extends Controller
{
//    public function __construct(
//        private readonly UserWalletService $walletService,
//    ) {}

    /**
     * Checkout from cart and create order
     *
     * POST /api/cart/checkout
     */
    public function checkout(Request $request): JsonResponse
    {

        $request->validate([
            'payment_method' => ['required', 'string', 'in:wallet,online'],
            'shipping_address_id' => ['required', 'string', 'exists:addresses,uuid'],
            'billing_address_id' => ['nullable', 'string', 'exists:addresses,uuid'],
            'billing_is_shipping' => ['boolean'],
            'gift' => ['boolean'],
            'pin' => ['nullable']
        ]);

        // Get User
        $user = $request->user();

        // Init Cart Service For That User
        $cartService = new CartService($user);
        $cartService->capture($request);
        // If No Items Found In Cart
        if ($cartService->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty',
            ], 400);
        }


        // Prepare Base Parameters
        $user->load('addresses');
        // Addresses For Order
        $shippingAddress = $user->addresses->firstWhere('uuid',$request->shipping_address_id);
        $billingAddress = $request->billing_is_shipping ? $shippingAddress : $user->addresses->firstWhere('uuid',$request->billing_address_id);

        // Validate Cart With Shipping Address
        $validation = $cartService->validate($shippingAddress);
        // If Not Valid
        if (! $validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Cart validation failed',
                'errors' => $validation['errors'],
            ], 422);
        }

        $cartTotal = $validation['cart_total'];
        $paymentMethod = $request->payment_method == 'wallet' ? $request->payment_method : PaymentMethodCast::CASHFREE->value;
        $paymentMethod = PaymentMethodCast::tryFrom($paymentMethod);

        try {
            return DB::transaction(function () use ($user, $cartTotal, $paymentMethod, $request, $cartService,$shippingAddress,$billingAddress) {

                $orderService = OrderCreationService::make($cartService,null);
                // 1. Create Order
                $order = $orderService->createOrder($user,$cartTotal,$shippingAddress,$billingAddress);

                // 2. Add Order Items
                foreach ($cartTotal['items'] as $item) {
                    $orderItem = $orderService->createOrderItem($item);
                }
                // 3. Clear Cart
                $cartService->empty();

                // 4. Handle Wallet Payment directly if chosen
                if ($paymentMethod === PaymentMethodCast::WALLET) {
                    $orderService->payWithWallet($user);
                    $order = $orderService->getOrder();
                    $transaction = $orderService->getTransaction();
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
                $orderService->payWithOnline($user,$paymentMethod);
                $order = $orderService->getOrder();
                $transaction = $orderService->getTransaction();

                return response()->json([
                    'success' => true,
                    'message' => 'Order placed. Redirecting to payment...',
                    'data' => [
                        'order_uuid' => $order->uuid,
                        'order_number' => $order->order_number,
                        'checkout_url' => route('checkout', ['transaction' => $transaction->uuid]),
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
