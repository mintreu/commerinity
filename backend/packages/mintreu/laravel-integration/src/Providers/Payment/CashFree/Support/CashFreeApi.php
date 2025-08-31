<?php

namespace Mintreu\LaravelIntegration\Providers\Payment\CashFree\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class CashFreeApi
{
    protected string $key;
    protected string $secret;
    protected string $apiVersion;
    protected Client $client;
    protected bool $assoc;

    public function __construct(string $key, string $secret, ?string $apiVersion = null, bool $assoc = true)
    {
        $this->key        = $key;
        $this->secret     = $secret;
        $this->apiVersion = $apiVersion ?? config('laravel-integration.providers.payments.cash-free.api_version', '2025-01-01');
        $this->assoc      = $assoc;

        $this->client = new Client([
            'base_uri' => rtrim($this->getBaseUrl(), '/') . '/',
            'timeout'  => config('laravel-integration.providers.payments.cash-free.timeout', 30),
        ]);
    }

    protected function getBaseUrl(): string
    {
        return config('laravel-integration.providers.payments.cash-free.dev', true)
            ? config('laravel-integration.providers.payments.cash-free.api.test', 'https://sandbox.cashfree.com/pg/')
            : config('laravel-integration.providers.payments.cash-free.api.prod', 'https://api.cashfree.com/pg/');
    }

    protected function getHeaders(): array
    {
        return [
            'Accept'          => 'application/json',
            'Content-Type'    => 'application/json',
            'x-api-version'   => $this->apiVersion,
            'x-client-id'     => $this->key,
            'x-client-secret' => $this->secret,
        ];
    }

    private function makeRequest(string $endpoint, array $payload = [], string $method = 'GET'): array
    {
        try {
            $options = [
                'headers' => $this->getHeaders(),
            ];

            if (strtoupper($method) === 'GET') {
                $options['query'] = $payload;
            } else {
                $options['json'] = $payload;
            }

            $response = $this->client->request($method, ltrim($endpoint, '/'), $options);

            return [
                'success'     => true,
                'status_code' => $response->getStatusCode(),
                'data'        => json_decode($response->getBody()->getContents(), $this->assoc),
            ];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $errorData = $response ? json_decode($response->getBody()->getContents(), true) : null;

            logger()->error('CashFree API RequestException', [
                'endpoint' => $endpoint,
                'payload'  => $payload,
                'error'    => $e->getMessage(),
                'response' => $errorData,
            ]);

            return [
                'success' => false,
                'error'   => [
                    'message'  => $e->getMessage(),
                    'code'     => $e->getCode(),
                    'response' => $errorData,
                ],
            ];
        } catch (GuzzleException $e) {
            logger()->error('CashFree API GuzzleException', [
                'endpoint' => $endpoint,
                'payload'  => $payload,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => [
                    'message'  => $e->getMessage(),
                    'code'     => $e->getCode(),
                    'response' => null,
                ],
            ];
        }
    }

    // === Generic Methods ===

    public function get(string $endpoint, array $payload = []): array
    {
        return $this->makeRequest($endpoint, $payload, 'GET');
    }

    public function fetch(string $endpoint, array $payload = []):array
    {
        return $this->get($endpoint,$payload);
    }

    public function post(string $endpoint, array $payload): array
    {
        return $this->makeRequest($endpoint, $payload, 'POST');
    }


}
