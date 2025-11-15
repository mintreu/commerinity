<?php

namespace App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz;

use App\Models\System\Provider;
use App\Services\ProviderServices\PaymentService\Contracts\ActionContract\OrderActionContract;
use App\Services\ProviderServices\PaymentService\Contracts\ActionContract\UtilityActionContract;
use App\Services\ProviderServices\PaymentService\Contracts\ActionContract\VerifyActionContract;
use App\Services\ProviderServices\PaymentService\Contracts\PaymentServiceContract;
use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Actions\LinkAction;
use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Actions\OrderAction;
use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Actions\UtilityAction;
use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Actions\VerifyAction;
use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Support\LaravelEaseBuzzApi;
use Easebuzz\PayWithEasebuzzLaravel\PayWithEasebuzzLib;
use Illuminate\Database\Eloquent\Model;

class LaravelEaseBuzzPayment implements PaymentServiceContract
{
    protected string $merchant_key;
    protected string $salt;
    protected string $env;
    private ?string $webhook = null;
    protected PayWithEasebuzzLib $api;
    protected ?string $error = null;
    protected Model|Provider $providerModel;


    public function __construct()
    {
        $this->merchant_key = config('services.payment.providers')['easebuzz']['key'];
        $this->salt = config('services.payment.providers')['easebuzz']['secret'];
        $this->env = config('services.payment.providers')['easebuzz']['env'];
        $this->api = new PayWithEasebuzzLib($this->merchant_key, $this->salt, $this->env);
    }

    public static function make(): LaravelEaseBuzzPayment
    {
        return app(LaravelEaseBuzzPayment::class)->getInstance();
    }

    protected function getInstance(): LaravelEaseBuzzPayment
    {
        return $this;
    }

    public function getApi():object
    {
        return $this->api;
    }

    public function getEnv():string
    {
        return $this->env;
    }

    public function getKey():string
    {
        return $this->merchant_key;
    }

    public function getSecret():string
    {
        return $this->salt;
    }
    public function getWebhookSecret(): string
    {
        return $this->webhook;
    }

    public function setModel(Model|Provider $model)
    {
        $this->providerModel = $model;
        return $this;
    }

    public function getModel():Model
    {
        return $this->providerModel;
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




    public function order():OrderActionContract
    {
        return OrderAction::make($this->api);
    }

    public function verify():VerifyActionContract
    {
        return VerifyAction::make($this->api);
    }

    public function link():LinkAction
    {
        return LinkAction::make(new LaravelEaseBuzzApi($this));
    }

    public function utility():UtilityActionContract
    {
        return UtilityAction::make(new LaravelEaseBuzzApi($this));
    }

    public function refund()
    {

    }

    public function qr()
    {

    }



    public function customer()
    {

    }


}
