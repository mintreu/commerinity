<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\CashFree\Support;

use Mintreu\LaravelIntegration\Providers\Payment\CashFree\CashFreePaymentProvider;
use Mintreu\LaravelIntegration\Support\ProviderOrder;
use Mintreu\LaravelIntegration\Traits\NormalizesTransactionResponse;

class CashFreeOrderResponse
{
    use NormalizesTransactionResponse;
    protected const STANDARD = 'standard';
    protected const LINK = 'payment_link';
    protected const QR = 'qr';
    protected array $responseData = [];
    protected CashFreePaymentProvider $provider;
    protected array $data = [];
    protected array $notes = [];
    protected array $response = [];

    /**
     * @param CashFreePaymentProvider $provider
     */
    public function __construct(CashFreePaymentProvider $provider)
    {
        $this->provider = $provider;
    }


    public static function make(CashFreePaymentProvider $provider)
    {
        return new static($provider);
    }

    public function capture(array $response, array $data)
    {
        $this->response = $response;
        $this->responseData = $response;

        // Check for errors in the response and set them in the provider
        if (isset($this->responseData['error'])) {
            $this->provider->setError($this->responseData['error']['message']);
        }
        $this->data = $data;
        $this->notes = $data['notes'] ?? [];
        return $this;
    }


    public function getOrderResponse(string $type = self::STANDARD):array
    {
        return $this->buildResponse($type);
    }


}
