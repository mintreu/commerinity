<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\Razorpay;

use Mintreu\LaravelIntegration\Providers\Payment\Razorpay\Actions\OrderAction;
use Razorpay\Api\Api;
use Mintreu\LaravelIntegration\Contracts\ProviderIntegrationContract;


class RazorpayPaymentProvider
{
    protected $integrationLoader;

    private string $key;
    private string $secret;
    private ?string $webhook = null;
    protected ?string $error = null;
    protected ?Api $api = null;

    /**
     * @param callable $integrationLoader
     */
    public function __construct(callable $integrationLoader)
    {
        $this->integrationLoader = $integrationLoader;
        $this->webhook = config('laravel-integration.providers.payment.razorpay.webhook');
    }



    public static function make():static
    {
        return app(static::class)->getInstance();
    }

    protected function getIntegration()
    {
        return ($this->integrationLoader)();
    }

    public function getSlug():string
    {
        return 'razorpay-payment';
    }

    public function getModel()
    {
        return $this->getIntegration();
    }

    /**
     * Set an error message.
     *
     * @param string $error_text
     */
    public function setError(string $error_text): void
    {
        $this->error = $error_text;
    }

    /**
     * Get the error message.
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    public function getApi(): Api
    {
        if (!$this->api) {
            $integration = $this->getIntegration();

            // fallback to config if DB record not found
            $key = $integration->key ?? config('laravel-integration.providers.payment.razorpay.key');
            $secret = $integration->secret ?? config('laravel-integration.providers.payment.razorpay.secret');

            $this->api = new Api($key, $secret);
        }

        return $this->api;
    }

    public function getWebHook()
    {
        return $this->webhook;
    }


    // Actions

    public function order(): Actions\OrderAction
    {
        return new OrderAction($this);
    }

    public function refund(): Actions\RefundAction
    {
        return new Actions\RefundAction($this);
    }

    public function verify(): Actions\VerifyAction
    {
        return new Actions\VerifyAction($this);
    }

    public function qr(): Actions\QRCodeAction
    {
        return new Actions\QRCodeAction($this);
    }

    public function link(): Actions\PaymentLinkAction
    {
        return new Actions\PaymentLinkAction($this);
    }


}
