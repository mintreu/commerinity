<?php

namespace App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz;

use App\Models\System\Provider;

use App\Services\ProviderServices\PaymentService\Contracts\ActionContract\UtilityActionContract;
use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Actions\UtilityAction;
use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Support\LaravelEaseBuzzApi;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\BeneficiaryActionContract;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\ContactActionContract;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\LedgerActionContract;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\PayoutActionContract;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\UpiActionContract;
use App\Services\ProviderServices\PayoutService\Contracts\PayoutServiceContract;
use App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz\Actions\BeneficiaryAction;
use App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz\Actions\ContactAction;
use App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz\Actions\LedgerAction;
use App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz\Actions\PayoutAction;
use App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz\Actions\UpiAction;
use App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz\Support\HasEaseBuzzRequest;
use Easebuzz\PayWithEasebuzzLaravel\PayWithEasebuzzLib;
use Illuminate\Database\Eloquent\Model;

class LaravelEaseBuzzPayout implements PayoutServiceContract
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
        $this->merchant_key = config('services.payout.providers')['easebuzz_payout']['key'];
        $this->salt = config('services.payout.providers')['easebuzz_payout']['secret'];
        $this->env = config('services.payout.providers')['easebuzz_payout']['env'];
        $this->api = new PayWithEasebuzzLib($this->merchant_key, $this->salt, $this->env);
    }

    public static function make(): LaravelEaseBuzzPayout
    {
        return app(LaravelEaseBuzzPayout::class)->getInstance();
    }

    protected function getInstance(): LaravelEaseBuzzPayout
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


    public function setModel(Model|Provider $model): static
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


    /**
     * @return UtilityActionContract
     */
    public function utility(): UtilityActionContract
    {
        return UtilityAction::make(new LaravelEaseBuzzApi($this));
    }

    public function contact():ContactActionContract
    {
        return new ContactAction(new LaravelEaseBuzzApi($this));
    }

    public function beneficiary():BeneficiaryActionContract
    {
        return BeneficiaryAction::make(new LaravelEaseBuzzApi($this));
    }


    public function payout():PayoutActionContract
    {
        return PayoutAction::make($this->api);
    }

    public function upi(Model $user):UpiActionContract
    {
        return UpiAction::make(new LaravelEaseBuzzApi($this),$user);
    }





    public function ledger():LedgerActionContract
    {
        return LedgerAction::make(new LaravelEaseBuzzApi($this));
    }





}
