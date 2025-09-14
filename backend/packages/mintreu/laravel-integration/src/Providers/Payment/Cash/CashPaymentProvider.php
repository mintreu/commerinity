<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\Cash;

class CashPaymentProvider
{


    protected $integrationLoader;
    protected ?string $error = null;


    /**
     * @param callable $integrationLoader
     */
    public function __construct(callable $integrationLoader)
    {
        $this->integrationLoader = $integrationLoader;
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
        return 'cash';
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


    public function order()
    {
        return new OrderAction($this);
    }

    public function verify()
    {
        return new VerifyAction($this);
    }



}
