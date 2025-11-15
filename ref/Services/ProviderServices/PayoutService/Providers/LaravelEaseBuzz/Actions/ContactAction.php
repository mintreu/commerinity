<?php

namespace App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz\Actions;

use App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Support\LaravelEaseBuzzApi;
use App\Services\ProviderServices\PayoutService\Contracts\ActionContract\ContactActionContract;
use Illuminate\Support\Str;

class ContactAction implements ContactActionContract
{

    protected LaravelEaseBuzzApi $api;


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
     * @return mixed
     * @throws \Throwable
     */
    public function create(array $data)
    {
        // Validate required keys in the input array
//        $requiredKeys = ['name', 'email', 'mobile'];
//        $missingKeys = array_diff($requiredKeys, array_keys($data));
//
//        if (!empty($missingKeys)) {
//            throw new \InvalidArgumentException(
//                'The following keys are missing: ' . implode(', ', $missingKeys)
//            );
//        }

        // Generate the payload
        $payload = [
            'key' => $this->api->getKey(),
            'name' => $data['name'],
            'email' => $data['email'] ?? '',
            'phone' => $data['mobile'],
        ];

        $hashAbleValue = $data['name'] . '|' . $data['email'] . '|' . $data['mobile'];

        // Generate the SHA-512 hash for Authorization header
        $authorization = $this->api->withHas($hashAbleValue)->getGeneratedAuthHash();

        // Add headers and send the request
        return $this->api
            ->setBaseUrl('https://stoplight.io/mocks/easebuzz/neobanking/90193133/api/',true)
            ->fetchPost('contacts/', $payload, $authorization);
    }


    /**
     * @param string $id
     * @return array
     */
    public function find(string $id):array
    {
        // Generate the SHA-512 hash for Authorization header
        $authorization = $this->api->withHas($id)->getGeneratedAuthHash();

        // Add headers and send the request
        $url = 'contacts/' . $id . '/?key=' . $this->api->getKey();

        return $this->api
            ->setBaseUrl('https://stoplight.io/mocks/easebuzz/neobanking/90193133/api/',true)
            ->fetchGet($url, $authorization);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        // TODO: Implement findAll() method.
    }

    /**
     * @param string $id
     * @param array $data
     * @return array
     */
    public function edit(string $id, array $data)
    {
        // TODO: Implement edit() method.
    }
}
