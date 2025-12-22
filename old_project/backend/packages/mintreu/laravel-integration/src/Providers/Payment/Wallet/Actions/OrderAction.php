<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\Wallet\Actions;

use Closure;
use Illuminate\Support\Str;
use Mintreu\LaravelIntegration\Providers\Payment\Cash\Support\OrderWrapper;
use Mintreu\LaravelIntegration\Providers\Payment\Wallet\WalletPaymentProvider;
use Mintreu\LaravelIntegration\Support\ProviderOrder;
use Mintreu\LaravelMoney\LaravelMoney;

class OrderAction
{

    protected WalletPaymentProvider $provider;

    /**
     * @param WalletPaymentProvider $provider
     */
    public function __construct(WalletPaymentProvider $provider)
    {
        $this->provider = $provider;
    }



    public function create(ProviderOrder|array|Closure $data): array
    {
        if ($data instanceof Closure) {
            $order = new ProviderOrder();
            $data($order); // execute closure, fill order
            $data = $order;
        }

        $data = OrderWrapper::make($data)->toArray();

        return [
            'success' => true,
            'error' => null,
            'provider' => $this->provider?->getSlug(),
            'checkout_type' => 'standard',
            'data' => [
                'checkout_type' => 'standard',
                // Provider order id (multiple fallbacks)
                'provider_gen_id' => 'wallet_'.Str::ulid(),
                // Provider generated links
                'provider_gen_link' => null,
                'provider_gen_session' => null,
                'provider_transaction_id' => '',
                'provider_generated_sign' => '',
                // Amount fallback chain
                'amount' => LaravelMoney::make($data['amount'])->getAmount(),
                // URLs
                'success_url' => '',
                'failure_url' => '',
                // Integration ID
                'integration_id' => $this->provider?->getModel()?->id,

                // Extra info
                'metadata' => [
                    'provider' => $data,
                    'additional' => $this->notes ?? [],
                ],
            ],

            // Keep raw response as array
            'response' => json_decode(json_encode($data), true),
        ];
    }






}
