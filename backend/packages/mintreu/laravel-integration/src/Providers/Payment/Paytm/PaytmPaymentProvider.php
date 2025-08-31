<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\Paytm;

class PaytmPaymentProvider
{


    protected $integrationLoader;
    protected ?string $key = null;
    protected ?string $secret = null;
    private ?string $webhook = null;
    protected ?string $error = null;

    /**
     * @param callable $integrationLoader
     */
    public function __construct(callable $integrationLoader)
    {
        $this->integrationLoader = $integrationLoader;
        $this->webhook = config('laravel-integration.providers.payment.paytm.webhook');
    }


    public static function make(?string $key, ?string $secret):static
    {
        return new static($key,$secret);
    }

    protected function getIntegration()
    {
        return ($this->integrationLoader)();
    }

    public function getSlug():string
    {
        return 'paytm-payment';
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



}
