<?php

namespace Mintreu\LaravelIntegration\Traits;

use Illuminate\Support\Arr;

trait NormalizesTransactionResponse
{
    /**
     * Normalize provider response to a standard structure
     * Structured Payment Provider Response For Easy use with
     * Transaction model. data key hold transaction model compatible data sets..
     *
     * @param string $type
     * @return array
     */
    protected function buildResponse(string $type): array
    {
        $responseData = $this->responseData ?? [];
        $data = $this->data ?? [];
        $provider = $this->provider ?? null;

        return [
            'success' => $provider && $provider->getError() === null,
            'error' => $provider?->getError(),
            'provider' => $provider?->getSlug(),
            'checkout_type' => $type,

            'data' => [
                'checkout_type' => $type,

                // Provider order id (multiple fallbacks)
                'provider_gen_id' => Arr::get($responseData, 'id')
                    ?? Arr::get($responseData, 'data.order_id')
                        ?? Arr::get($responseData, 'data.cf_order_id'),

                // Provider generated links
                'provider_gen_link' => Arr::get($responseData, 'data.payment_link')
                    ?? Arr::get($responseData, 'image_url')
                    ?? Arr::get($responseData, 'short_url'),

                'provider_gen_session' => Arr::get($responseData, 'data.payment_session_id'),
                'provider_gen_qr' => Arr::get($responseData, 'data.payment_qr'),


                'provider_transaction_id' => Arr::get($responseData, 'data.transaction_id'),
                'provider_generated_sign' => Arr::get($responseData, 'data.signature'),

                // Amount fallback chain
                'amount' => Arr::get($responseData, 'data.amount')
                    ?? Arr::get($responseData, 'amount')
                        ?? Arr::get($responseData, 'payment_amount')
                        ?? Arr::get($data, 'amount', 0),

                // URLs
                'success_url' => Arr::get($data, 'order_meta.return_url', Arr::get($data, 'success_url', '')),
                'failure_url' => Arr::get($data, 'order_meta.notify_url', Arr::get($data, 'failure_url', '')),



                // Integration ID
                'integration_id' => $provider?->getModel()?->id,

                // Expiry
                'expire_at' => Arr::get($data, 'expire_at', Arr::get($responseData, 'data.order_expiry_time')),

                // Extra info
                'metadata' => [
                    'provider' => $responseData,
                    'additional' => $this->notes ?? [],
                ],
            ],

            // Keep raw response as array
            'response' => json_decode(json_encode($this->response), true),
        ];
    }
}
