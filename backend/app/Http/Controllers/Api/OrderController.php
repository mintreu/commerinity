<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CustomerOrderRequest;
use App\Http\Requests\Order\GuestOrderRequest;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Http\Resources\Order\OrderIndexResource;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order\Order;
use App\Services\OrderService\OrderCreationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Mintreu\LaravelGeokit\Casts\AddressTypeCast;
use Mintreu\LaravelGeokit\Models\Address;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;



class OrderController extends Controller
{


    public function getAllOrders(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = $user->orders()->newQuery();

        // ✅ Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // ✅ Filter by date range
        if ($from = $request->input('from_date')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to_date')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return OrderIndexResource::collection(
            $query->latest()->paginate(10) // ✅ pagination
        );
    }


    public function getOrderDetail(Order $order,Request $request): OrderResource
    {
        $order->load(['transaction','billingAddress','shippingAddress','orderProducts.product']);

        return OrderResource::make($order);
    }

















    public function placeOrder(PlaceOrderRequest $request)
    {


        $validated = $request->validated();
        $user = $request->user();


        // ✅ Now only DB work inside transaction
        return DB::transaction(function () use ($request, $user, $validated) {
            $orderService = OrderCreationService::make($user);

            if (auth('sanctum')->check()) {
                $billingAddress  = Address::where('uuid', $validated['billing_address'])->first();
                $shippingAddress = Address::where('uuid', $validated['shipping_address'])->first();

                if ($validated['billing_is_shipping'] && $billingAddress && $shippingAddress) {
                    if ($billingAddress->id === $shippingAddress->id) {
                        $orderService->billingAddress($billingAddress)
                            ->shippingAddress($shippingAddress);
                    } else {
                        return response()->json([
                            'data' => [
                                'success' => false,
                                'message' => 'Shipping and Billing address not matched',
                            ]
                        ], 403);
                    }
                } else {
                    return response()->json([
                        'data' => [
                            'success' => false,
                            'message' => 'Invalid address selected.',
                        ]
                    ], 403);
                }
            } else {

                $deliveryAddress = Address::create([
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


                $orderService->billingAddress($deliveryAddress)
                    ->shippingAddress($deliveryAddress);
            }

            // Process checkout
            $checkoutUrl = $orderService
                ->paymentProvider($validated['payment_provider'])
                ->asGift($validated['gift'])
                ->placeOrder($request)
                ->getCheckoutUrl();

            $error = $orderService->getError();


            return response()->json([
                'data' => [
                    'success'      => is_null($error),
                    'checkout_url' => $checkoutUrl,
                    'message'       => $error,
                ],
            ]);
        });
    }



}
