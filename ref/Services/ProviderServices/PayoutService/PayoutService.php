<?php

namespace App\Services\ProviderServices\PayoutService;

use App\Models\Enums\Wallet\ProviderTypeCast;
use App\Models\System\Provider;
use App\Services\ProviderServices\PaymentService\Contracts\PaymentServiceContract;
use App\Services\ProviderServices\PayoutService\Contracts\PayoutServiceContract;
use Illuminate\Support\Facades\Cache;

class PayoutService
{


    protected array $availableProviders = [];
    protected ?PayoutServiceContract $activeProvider = null;
    protected $payoutProviderRecords;

    public function __construct(?string $url = null)
    {
        // Load all Payment providers from config
        $allPayoutProviders = config('services.payout.providers');


        $this->payoutProviderRecords = Cache::remember('active_payout_providers', now()->addMinutes(5), function () {
            return Provider::where('type', ProviderTypeCast::PAYOUT)
                ->where('status', true)
                ->get();
        });




        foreach ($allPayoutProviders as $provider => $config) {
            $this->availableProviders[$provider] = $this->getProviderInstance($config);
        }

        // Get the active provider based on the status and balance check logic
        $this->activeProvider = $this->getActiveProvider($url);




    }

    public static function make(): PayoutServiceContract
    {
        $instance = new static();
        return $instance->activeProvider;
    }




    protected function getProviderInstance(array $config): PayoutServiceContract
    {
        $class = $config['provider'];
        // Retrieve the provider record from the cached data
        $providerRecord = $this->payoutProviderRecords
            ->where('type', ProviderTypeCast::PAYOUT)
            ->where('status', true)
            ->where('url', $config['url']) // Assuming the provider name matches the config key
            ->first();


        return $class::make()->setModel($providerRecord);
    }

    protected function getActiveProvider(?string $url = null): PayoutServiceContract
    {




        if ($this->payoutProviderRecords->count())
        {
            $this->activeProvider = $this->payoutProviderRecords->where('url',$url)->first();
        }

        if (is_null($this->activeProvider))
        {
            $this->activeProvider = $this->availableProviders[$url] ?? $this->availableProviders[array_keys($this->availableProviders)[0]];
        }

        return $this->activeProvider;
    }






}
