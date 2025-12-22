<?php

namespace Mintreu\LaravelIntegration\Providers\Sms\Fast2Sms;

use Mintreu\LaravelIntegration\Contracts\ProviderIntegrationContract;

class Fast2SmsProvider
{

    protected ?string $key = null;
    protected ?string $secret = null;

    /**
     * @param string|null $key
     * @param string|null $secret
     */
    public function __construct(?string $key, ?string $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    public static function make(?string $key, ?string $secret):static
    {
        return new static($key,$secret);
    }

    public function getSlug():string
    {
        return 'fast2sms-sms';
    }


}
