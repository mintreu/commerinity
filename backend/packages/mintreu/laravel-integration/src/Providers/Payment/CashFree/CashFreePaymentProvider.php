<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\CashFree;


use Mintreu\LaravelIntegration\Providers\Payment\CashFree\Actions\OrderAction;
use Mintreu\LaravelIntegration\Providers\Payment\CashFree\Actions\VerifyAction;
use Mintreu\LaravelIntegration\Providers\Payment\CashFree\Support\CashFreeApi;

class CashFreePaymentProvider
{

    protected $integrationLoader;
    protected ?string $key = null;
    protected ?string $secret = null;
    private ?string $webhook = null;
    protected ?string $error = null;
    protected ?CashFreeApi $api = null;

    /**
     * @param callable $integrationLoader
     */
    public function __construct(callable $integrationLoader)
    {
        $this->integrationLoader = $integrationLoader;
        $this->webhook = config('laravel-integration.providers.payment.cash-free.webhook');
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
        return 'cash-free-payment';
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


    public function getApi():CashfreeApi
    {
        if (!$this->api) {
            $integration = $this->getIntegration();

            // fallback to config if DB record not found
            $key    = $integration->key ?? config('laravel-integration.providers.payment.cash-free.key');
            $secret = $integration->secret ?? config('laravel-integration.providers.payment.cash-free.secret');
            $env    = $integration->env ?? config('laravel-integration.providers.payment.cash-free.env', 'sandbox');

            $environment = strtolower($env) === 'production'
                ? 1 // PRODUCTION
                : 0; // SANDBOX

            $this->api = new CashFreeApi($key,$secret);
        }

        return $this->api;
    }





    public function order()
    {
        return new OrderAction($this);
    }

    public function verify()
    {
        return new VerifyAction($this);
    }



}
