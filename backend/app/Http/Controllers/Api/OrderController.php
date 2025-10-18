<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Http\Resources\Order\OrderIndexResource;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order\Order;
use App\Services\OrderService\OrderCreationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Mintreu\LaravelGeokit\Casts\AddressTypeCast;
use Mintreu\LaravelGeokit\Models\Address;


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


    public function getInsight(Request $request): \Illuminate\Http\JsonResponse
    {
        $user   = $request->user();
        $range  = $request->query('range', 'year');          // today|week|month|year
        $metric = $request->query('metric', 'count');        // count|revenue
        $status = $request->query('status');                 // array|string|null

        // If no status provided, don’t filter by status at all
        $statusArray = is_array($status) ? $status : (is_string($status) ? [$status] : []);

        [$start, $end, $interval] = match ($range) {
            'today' => [now()->startOfDay(), now()->endOfDay(), 'perHour'],
            'week'  => [now()->startOfWeek(), now()->endOfWeek(), 'perDay'],
            'month' => [now()->startOfMonth(), now()->endOfMonth(), 'perDay'],
            default => [now()->startOfYear(), now()->endOfYear(), 'perMonth'],
        };

        $query = Order::query()
            ->where('customerable_type', get_class($user))
            ->where('customerable_id', $user->getKey());

        if (!empty($statusArray)) {
            $query->whereIn('status', $statusArray);
        }

        $builder = Trend::query($query)->between(start: $start, end: $end);

        $builder = match ($interval) {
            'perHour' => $builder->perHour(),
            'perDay'  => $builder->perDay(),
            default   => $builder->perMonth(),
        };

        $data = $metric === 'revenue'
            ? $builder->sum('total')
            : $builder->count();

        $labels = $data->map(fn (TrendValue $v) => $v->date);
        $values = $data->map(fn (TrendValue $v) => $v->aggregate);

        return response()->json([
            'data' => [
                'datasets' => [
                    [
                        'label' => $metric === 'revenue' ? 'Revenue' : 'Orders',
                        'data'  => $values,
                    ],
                ],
                'labels' => $labels,
                'meta' => [
                    'range'    => $range,
                    'metric'   => $metric,
                    'status'   => $statusArray,
                    'interval' => $interval,
                    'start'    => $start->toISOString(),
                    'end'      => $end->toISOString(),
                ],
            ],
        ]);
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

            $checkoutUrl = null;
            if ($validated['payment_provider'] == 'wallet-payment')
            {
                // Pay And Confirm With Wallet
                // Process checkout
                $transaction = $orderService
                    ->paymentProvider($validated['payment_provider'])
                    ->asGift($validated['gift'])
                    ->placeOrder($request)
                    ->getTransaction();

                $checkoutUrl = $transaction->verified ? $transaction->success_redirect_url : $transaction->failure_redirect_url;

            }else{
                // Process checkout
                $checkoutUrl = $orderService
                    ->paymentProvider($validated['payment_provider'])
                    ->asGift($validated['gift'])
                    ->placeOrder($request)
                    ->getCheckoutUrl();
            }



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






    public function getOrderInvoicePdf(Order $order,Request $request)
    {
       // $order->load(['transaction','billingAddress','shippingAddress','orderProducts.product']);

        $order->load([
            'invoices'
        ]);


        $pdf = Pdf::loadView('invoices.demo_invoice',[
            'logo' => asset('images/logo.png'),
            'company' => [
                'name' => config('app.name'),
                'email' => config('app.mail'),
            ],
            'invoices' => $order->invoices
        ])->setPaper('a4')->setWarnings(false);

        //return $pdf->stream();

        // Return as download
        return $pdf->download("Invoice-{$order->uuid}.pdf");

    }




}
