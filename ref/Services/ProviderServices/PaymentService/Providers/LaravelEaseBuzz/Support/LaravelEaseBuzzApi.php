<?php
namespace App\Services\ProviderServices\PaymentService\Providers\LaravelEaseBuzz\Support;

use App\Services\ProviderServices\PaymentService\Contracts\PaymentServiceContract;
use App\Services\ProviderServices\PayoutService\Contracts\PayoutServiceContract;
use Easebuzz\PayWithEasebuzzLaravel\PayWithEasebuzzLib;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class LaravelEaseBuzzApi
{
    protected PaymentServiceContract|PayoutServiceContract $provider;
    protected PayWithEasebuzzLib $api;
    protected Client $client;
    protected ?string $version = 'v1';
    protected ?string $baseUrl = null;
    protected ?string $generatedAuthHash = null;

    protected ?string $apiKey = null;

    public function __construct(PaymentServiceContract|PayoutServiceContract $provider)
    {
        $this->provider = $provider;
        $this->api = $this->provider->getApi();
        $this->apiKey = $this->provider->getKey();
        $this->client = new Client();
        $this->setBaseUrl();
    }

    public function getKey(): ?string
    {
        return $this->apiKey;
    }

    public function version(?string $version = null): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Set API base URL based on environment.
     */
    public function setBaseUrl(?string $baseUrl = null,bool $testing = false): static
    {
        if ($this->provider->getEnv() === 'production')
        {
            $this->baseUrl = 'https://wire.easebuzz.in/api/';
        }else{

            $this->baseUrl = $testing ? $baseUrl : 'https://stoplight.io/mocks/easebuzz/neobanking/124315382/api/';
        }

        return $this;
    }

    /**
     * Generate the SHA-512 hash for Authorization header.
     */
    public function withHas(string $value, ?string $salt = null): static
    {
        $key = $this->provider->getKey(); // Get API key from provider
        $this->generatedAuthHash = hash('sha512', "{$key}|{$value}|{$salt}");
        return $this;
    }

    /**
     * Get the generated authorization hash.
     */
    public function getGeneratedAuthHash(): ?string
    {
        return $this->generatedAuthHash;
    }

    /**
     * Build headers for API requests.
     */
    protected function buildHeaders(string $authorization,bool $contentTypeJson = true): array
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => $authorization, // Generated Authorization hash
            //'Content-Type' => 'application/json',
            'Content-Type' => $contentTypeJson ? 'application/json' :'application/x-www-form-urlencoded',
            'WIRE-API-KEY' => $this->provider->getKey(),
        ];
    }

    /**
     * Make a POST request to the Easebuzz API.
     */
    public function fetchPost(string $endpoint, array $payload, string $authorization,bool $headerContentTypeJson = true): ?array
    {
        if (!is_null($this->version))
        {
            $url = $this->baseUrl . $this->version . '/' . $endpoint;
        }else{
            $url = $this->baseUrl . $endpoint;
        }


        try {
            $response = $this->client->request('POST', $url, [
                'json' => $payload,
                'headers' => $this->buildHeaders($authorization,$headerContentTypeJson),
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return [
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'response' => $response ? json_decode($response->getBody()->getContents(), true) : null,
                ],
            ];
        }
    }



    public function fetchGet(string $endpoint,string $authorization): array
    {
        $url = $this->baseUrl . $this->version . '/' . $endpoint;
        try {
            $header = $this->buildHeaders($authorization);
            unset($header['Content-Type']);
            $response = $this->client->request('GET', $url, [
                'headers' => $header,
            ]);

            return [
                'success' => true,
                'data' => json_decode($response->getBody()->getContents(), true),
            ];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            return [
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'response' => $response ? json_decode($response->getBody()->getContents(), true) : null,
                ],
            ];
        }
    }



}

