<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderIndexResource;
use App\Services\CartService\Cart;
use App\Services\OrderService\OrderCreationService;
use Illuminate\Http\Request;
use Mintreu\LaravelGeokit\Models\Address;


class OrderController extends Controller
{


    public function getAllOrderOfCustomer(Request $request)
    {
        $user = $request->user();
        $user->load('orders');
        return OrderIndexResource::collection($user->orders);
    }



    public function placeOrder(Request $request)
    {
        $user = $request->user();

        // Validation
        $validated = $request->validate([
            'billing_address'     => 'required|string|exists:addresses,uuid',
            'shipping_address'    => 'required|string|exists:addresses,uuid',
            'gift'                => 'required|boolean',
            'payment_provider'    => 'required|string|max:100',
            'billing_is_shipping' => 'required|boolean',
        ]);

        // Resolve addresses
        $billingAddress  = Address::where('uuid', $validated['billing_address'])->first();
        $shippingAddress = Address::where('uuid', $validated['shipping_address'])->first();

        // 🔐 Ensure addresses belong to the authenticated user
        if (
            !$user->addresses->contains($billingAddress->id) ||
            !$user->addresses->contains($shippingAddress->id)
        ) {
            return response()->json([
                'data' => [
                    'success' => false,
                    'message' => 'Invalid address selected.',
                ]
            ], 403);
        }

        // Build order
        $orderService = OrderCreationService::make($user);

        $checkoutUrl = $orderService
            ->shippingAddress($shippingAddress)
            ->billingAddress($billingAddress)
            ->paymentProvider($validated['payment_provider'])
            ->asGift($validated['gift'])
            ->placeOrder()
            ->getCheckoutUrl();

        return response()->json([
            'data' => [
                'success'      => true,
                'checkout_url' => $checkoutUrl,
            ],
        ]);
    }


}
