<?php

namespace App\Services\ProviderServices\PaymentService;

use App\Models\Enums\Wallet\ProviderTypeCast;
use App\Models\System\Provider;
use App\Services\ProviderServices\PaymentService\Contracts\PaymentServiceContract;
use Illuminate\Support\Facades\Cache;

class PaymentService
{

    protected array $availableProviders = [];
    protected ?PaymentServiceContract $activeProvider = null;
    protected $paymentProviderRecords;

    public function __construct(?string $url = null)
    {
        // Load all Payment providers from config
        $allPaymentProviders = config('services.payment.providers');

        $this->paymentProviderRecords = Cache::remember('active_payment_providers', now()->addMinutes(5), function () {
            return Provider::where('type', ProviderTypeCast::PAYMENT)
                ->where('status', true)
                ->get();
        });




        foreach ($allPaymentProviders as $provider => $config) {
            $this->availableProviders[$provider] = $this->getProviderInstance($config);
        }

        // Get the active provider based on the status and balance check logic
        $this->activeProvider = $this->getActiveProvider($url);




    }

    public static function make(): PaymentServiceContract
    {
        $instance = new static();
        return $instance->activeProvider;
    }




    protected function getProviderInstance(array $config): PaymentServiceContract
    {
        $class = $config['provider'];
        // Retrieve the provider record from the cached data
        $providerRecord = $this->paymentProviderRecords
            ->where('type', ProviderTypeCast::PAYMENT)
            ->where('status', true)
            ->where('url', $config['url']) // Assuming the provider name matches the config key
            ->first();


//        return $class::make($config['key'],$config['secret'])->setModel($providerRecord);
        return $class::make()->setModel($providerRecord);
    }

    protected function getActiveProvider(?string $url = null): PaymentServiceContract
    {




        if ($this->paymentProviderRecords->count())
        {
            $this->activeProvider = $this->paymentProviderRecords->where('url',$url)->first();
        }

        if (is_null($this->activeProvider))
        {
            $this->activeProvider = $this->availableProviders[$url] ?? $this->availableProviders[array_keys($this->availableProviders)[0]];
        }

        return $this->activeProvider;
    }

}
