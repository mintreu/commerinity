<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\Razorpay\Support;

use Mintreu\LaravelIntegration\Providers\Payment\Razorpay\RazorpayPaymentProvider;
use Mintreu\LaravelIntegration\Traits\NormalizesTransactionResponse;
use Razorpay\Api\Order;

class RazorpayOrderResponse
{
    use NormalizesTransactionResponse;
    protected const STANDARD = 'standard';
    protected const LINK = 'payment_link';
    protected const QR = 'qr';

    protected array $responseData = [];
    protected Order $response;
    protected RazorpayPaymentProvider $provider;
    protected array $data = [];
    protected array $notes = [];

    public function __construct(RazorpayPaymentProvider $provider)
    {
        $this->provider = $provider;
    }


    public static function make(RazorpayPaymentProvider $provider)
    {
        return new static($provider);
    }

    public  function capture(Order $response,array $data, array $notes =[]): static
    {
        $this->response = $response;
        $this->responseData = $response->toArray();
        // Check for errors in the response and set them in the provider
        if (isset($this->responseData['error'])) {
            $this->provider->setError($this->responseData['error']['description']);
        }
        $this->data = $data;
        $this->notes = $notes;
        return $this;
    }

    public function getOrderResponse(string $type = self::STANDARD):array
    {
        return $this->buildResponse($type);
    }


//    protected function buildResponse(string $type):array
//    {
//
//        return [
//            'success' => $this->provider->getError() === null,
//            'error' => $this->provider->getError(),
//            'data' => [
//                'provider_gen_id' => $this->responseData['id'], // Ensure consistency with API response
//                'provider_transaction_id' => null,
//                'provider_generated_sign' => null,
//                'amount' => $this->responseData['amount'] ?? $this->responseData['payment_amount'] ?? $this->data['amount'] ?? 0,
//                'callback_url' => $this->data['callback_url'] ?? '',
//                'provider_gen_url' => $this->responseData['image_url'] ?? $this->responseData['short_url'] ?? null,
//                'integration_id' => $this->provider->getModel()?->id ?? null,
//                'checkout_type' => $type,
//                'details' => [
//                    'provider' => $this->responseData,
//                    'additional' => $this->notes,
//                ],
//            ],
//            'provider' => $this->responseData,
//            'response' => json_decode(json_encode($this->response)),
//        ];
//    }



}
