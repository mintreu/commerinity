<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Models\User;
use App\Services\OrderService\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mintreu\LaravelCommerinity\Services\CartService\Cart;
use Mintreu\LaravelGeokit\Casts\AddressTypeCast;
use Mintreu\LaravelGeokit\Models\Address;

class OrderActionController extends Controller
{


    public function placeOrder(PlaceOrderRequest $request)
    {
        $validated = $request->validated();
        $customer = $request->user();


        if (!$customer)
        {
            $customer = User::create([
                'name' => $validated['customer_name'],
                'email' => $validated['customer_email'],
                'mobile' => $validated['customer_mobile'],
                'password' => Hash::make($validated['customer_mobile']),
            ]);

            $shippingAddress = $customer->address()->create([
                'block_id'        => $validated['block_id'],
                'address_1'      => $validated['address_1'],
                'city'           => $validated['city'],
                'postal_code'    => $validated['postal_code'],
                'person_name'    => $validated['customer_name'],
                'person_email'   => $validated['customer_email'],
                'person_mobile'  => $validated['customer_mobile'],
                'landmark'       => $validated['landmark'],
                'state_code'     => $validated['state'],
                'type'           => AddressTypeCast::DELIVERY,
                'title'          => 'Guest Delivery Address',
            ]);

            $billingAddress = $shippingAddress;


        }else{
            $billingAddress  = Address::where('uuid', $validated['billing_address'])->first();
            $shippingAddress = Address::where('uuid', $validated['shipping_address'])->first();
        }

        // Cart
        $cartService = new Cart($customer);
        $cartService->capture($request);

        $orderService = OrderService::make();
        $result = $orderService->place(cart: $cartService,billing: $billingAddress,shipping: $shippingAddress,provider: $validated['payment_provider']);


        return response()->json([
            'data' => [
                'success'      => is_null($result['errors']),
                'checkout_url' => $result['redirect'],
                'message'       => $result['errors'] ?? 'Order Placed Successfully!',
            ],
        ]);



    }




}
