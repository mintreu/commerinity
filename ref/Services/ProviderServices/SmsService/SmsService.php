<?php

namespace App\Services\ProviderServices\SmsService;

use App\Models\Enums\Wallet\ProviderTypeCast;
use App\Models\System\Provider;
use App\Services\MoneyService\Money;
use App\Services\ProviderServices\SmsService\Support\SmsServiceContract;
use Illuminate\Support\Facades\Cache;

class SmsService
{
    protected array $availableProviders = [];
    protected ?SmsServiceContract $activeProvider = null;
    protected $smsProviderRecords;

    public function __construct(?string $url = null)
    {
        // Load all SMS providers from config
        $allSmsProviders = config('services.sms.providers');
        foreach ($allSmsProviders as $provider => $config) {
            $this->availableProviders[$provider] = $this->getProviderInstance($config);
        }

        // Get the active provider based on the status and balance check logic
        $this->activeProvider = $this->getActiveProvider();
    }

    public static function make(): SmsServiceContract
    {
        $instance = new static();
        return $instance->activeProvider;
    }

    protected function getProviderInstance(array $config): SmsServiceContract
    {
        $class = $config['provider'];
        return $class::make();
    }

    /**
     * Get the active provider based on the current status and balance.
     *
     * @return SmsServiceContract
     */
    protected function getActiveProvider(): SmsServiceContract
    {
        // Cache the provider records to avoid hitting the DB multiple times
        $this->smsProviderRecords = Cache::remember('active_sms_providers', now()->addMinutes(5), function () {
            return Provider::where('type', ProviderTypeCast::SMS)
                ->where('status', true)
                ->get();
        });

        // Check if there are multiple active providers
        if ($this->smsProviderRecords->count() > 1) {
            // Iterate over providers to check balance
            foreach ($this->smsProviderRecords as $providerRecord) {
                if (is_null($this->activeProvider)) {
                    $smsProvider = $this->availableProviders[$providerRecord->url];
                    $smsProviderBalance = new Money($smsProvider->getBalance());

                    // If balance is not zero, set as active provider
                    if (!$smsProviderBalance->sameAs(0)) {
                        $this->activeProvider = $smsProvider;
                    }
                }
            }
        } else {
            // If only one active provider, use the default provider
            $defaultProvider = $this->smsProviderRecords->firstWhere('default', true)?->url ?? config('services.sms.default');
            $this->activeProvider = $this->availableProviders[$defaultProvider];
        }

        return $this->activeProvider;
    }
}
