<?php

namespace Mintreu\LaravelIntegration\Providers\Payout\Razorpay;

use Mintreu\LaravelIntegration\Contracts\IntegrationContract;

class RazorpayPayoutProvider implements IntegrationContract
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

    public function getIntegration()
    {
        return ($this->integrationLoader)();
    }


    public function getSlug():string
    {
        return 'razorpay-payout';
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
