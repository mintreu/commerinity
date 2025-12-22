<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\Razorpay\Actions;

use Closure;
use Mintreu\LaravelIntegration\Providers\Payment\Razorpay\RazorpayPaymentProvider;
use Mintreu\LaravelIntegration\Providers\Payment\Razorpay\Support\OrderWrapper;
use Mintreu\LaravelIntegration\Providers\Payment\Razorpay\Support\RazorpayOrderResponse;
use Mintreu\LaravelIntegration\Support\ProviderOrder;

/**
 * Class OrderAction
 *
 * Handles actions related to Razorpay orders, including creation, fetching, and retrieving all orders.
 */
class OrderAction
{

    /**
     * The instance of LaravelRazorpay service.
     *
     * @var RazorpayPaymentProvider
     */
    protected RazorpayPaymentProvider $provider;

    /**
     * OrderAction constructor.
     *
     * @param RazorpayPaymentProvider $razorpay
     */
    public function __construct(RazorpayPaymentProvider $razorpay)
    {
        $this->provider = $razorpay;
    }

    /**
     * Create a new Razorpay order.
     *
     * @param array $data The data for creating the order.
     * @param array $notes Optional notes to include with the order.
     * @return array The response from the Razorpay API, filtered by the specified notes and data.
     */
    public function create(ProviderOrder|array|Closure $data): array
    {
        if ($data instanceof Closure) {
            $order = new ProviderOrder();
            $data($order); // execute closure, fill order
            $data = $order;
        }

        $data = OrderWrapper::make($data)->toArray();

       // dd('razor',$data);


        // Create the order using Razorpay API
        $response = $this->provider->getApi()->order->create($data);



        return RazorpayOrderResponse::make($this->provider)->capture($response,$data,$data['notes'])
            ->getOrderResponse();

        // Filter and return the response data
       // return $this->getResponse($response->toArray(), $data, PaymentModelTypeCast::STANDARD, $notes);
    }

    /**
     * Fetch a Razorpay order by its identifier.
     *
     * @param string $id The identifier of the order to fetch.
     * @return mixed The order data returned by the Razorpay API.
     */
    public function fetch(string $id): mixed
    {
        return $this->provider->getApi()->order->fetch($id);
    }

    /**
     * Retrieve all Razorpay orders based on the provided data.
     *
     * @param array $data Optional data to filter the orders.
     * @return mixed The list of orders returned by the Razorpay API.
     */
    public function all(array $data = []): mixed
    {
        return $this->provider->getApi()->order->all($data);
    }







//
//    protected function getResponse(array $responseData)
//    {
//
//        // Check for errors in the response and set them in the provider
//        if (isset($responseData['error'])) {
//            $this->provider->setError($responseData['error']['description']);
//        }
//
//        return [
//            'success' => $this->provider->getError() === null,
//            'error' => $this->provider->getError(),
//            'data' => [
//                'provider_gen_id' => $responseData['id'], // Ensure consistency with API response
//                'provider_transaction_id' => null,
//                'provider_generated_sign' => null,
//                'amount' => $responseData['amount'] ?? $responseData['payment_amount'] ?? $data['amount'] ?? 0,
//                'callback_url' => $data['callback_url'] ?? '',
//                'provider_gen_url' => $responseData['image_url'] ?? $responseData['short_url'] ?? null,
//                'payment_provider_id' => $this->provider->getModel()?->id ?? null,
//                'type' => $type,
//                'details' => [
//                    'provider' => $responseData,
//                    'additional' => $notes,
//                ],
//            ],
//            'provider' => $responseData,
//            'response' => $response,
//        ];
//    }
//



}
