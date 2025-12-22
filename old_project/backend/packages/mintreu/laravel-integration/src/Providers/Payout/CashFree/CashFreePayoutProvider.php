<?php

namespace Mintreu\LaravelIntegration\Providers\Payout\CashFree;

use Mintreu\LaravelIntegration\Contracts\IntegrationContract;

class CashFreePayoutProvider implements IntegrationContract
{

    protected $integrationLoader;
    protected ?string $error = null;

    public function __construct(callable $integrationLoader)
    {
        $this->integrationLoader = $integrationLoader;
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
        return 'cash-free-payout';
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
