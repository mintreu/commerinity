<?php

namespace Mintreu\LaravelIntegration\Providers\Payout\Razorpay;

class RazorpayPayoutProvider
{


    protected $integrationLoader;
    protected ?Api $api = null;
    private string $key;
    private string $secret;
    private ?string $webhook = null;
    protected ?string $error = null;

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
        return 'razorpay-payout';
    }

    public function getApi(): ?Api
    {
        if (!$this->api) {
            $integration = $this->getIntegration();

            // fallback to config if DB record not found
            $key = $integration->key ?? config('laravel-integration.providers.payout.razorpay.key');
            $secret = $integration->secret ?? config('laravel-integration.providers.payout.razorpay.secret');

            $this->api = new Api($key, $secret);
        }

        return $this->api;
    }

    public function getModel()
    {
        return $this->getIntegration();
    }



}
