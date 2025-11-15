<?php

namespace App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz\Actions;

use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Support\LaravelEaseBuzzApi;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\LedgerActionContract;
use Illuminate\Database\Eloquent\Model;

/**
 * Docs : https://docs.easebuzz.in/docs/neobanking/ad239ee41678e-create-virtual-account
 */
class LedgerAction implements LedgerActionContract
{


    protected LaravelEaseBuzzApi $api;
    protected Model $user;

    public function __construct(LaravelEaseBuzzApi $api)
    {
        $this->api = $api;
    }

    public static function make(LaravelEaseBuzzApi $api):static
    {
        return new static($api);
    }


    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $payload = $data;
        $payload['key'] = $this->api->getKey();

        $hashAbleValue = $data['label'];

        // Generate the SHA-512 hash for Authorization header
        $authorization = $this->api->withHas($hashAbleValue,'saltvalue')->getGeneratedAuthHash();


        // Add headers and send the request
        return $this->api
            ->setBaseUrl('https://stoplight.io/mocks/easebuzz/neobanking/141702012/api/',true)
            ->fetchPost('insta-collect/virtual_accounts/', $payload, $authorization);
    }

    /**
     * @param string $id
     * @return array
     */
    public function find(string $id): array
    {
        // TODO: Implement find() method.
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        // TODO: Implement findAll() method.
    }

    /**
     * @param string $id
     * @param array $data
     * @return array
     */
    public function update(string $id, array $data): array
    {
        // TODO: Implement update() method.
    }

    /**
     * @return array
     */
    public function balance(): array
    {
        // TODO: Implement balance() method.
    }

    /**
     * @param string $id
     * @param bool $status
     * @return mixed
     */
    public function status(string $id, bool $status = false)
    {
        // TODO: Implement status() method.
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function collect(string $id)
    {
        // TODO: Implement collect() method.
    }
}
