<?php

declare(strict_types=1);

namespace App\Services\ShipmentProviders\Shiprocket;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ShiprocketApi
{
    protected ?string $email;

    protected ?string $password;

    protected string $baseUrl;

    protected int $tokenTtlMinutes;

    public function __construct(
        ?string $email = null,
        ?string $password = null,
        ?string $baseUrl = null,
        ?int $tokenTtlMinutes = null
    ) {
        $config = config('shipping.shiprocket', []);

        $this->email = $email ?? Arr::get($config, 'email');
        $this->password = $password ?? Arr::get($config, 'password');
        $this->baseUrl = rtrim($baseUrl ?? (string) Arr::get($config, 'base_url', ''), '/').'/';
        $this->tokenTtlMinutes = max(1, $tokenTtlMinutes ?? (int) Arr::get($config, 'token_ttl_minutes', 50));
    }

    /**
     * @throws RequestException
     */
    public function createForwardShipment(array $payload): array
    {
        return $this->request()
            ->post('shipments/create/forward-shipment', $payload)
            ->throw()
            ->json();
    }

    /**
     * @throws RequestException
     */
    public function trackByAwb(string $awb): array
    {
        return $this->request()
            ->get('courier/track/awb/'.urlencode($awb))
            ->throw()
            ->json();
    }

    /**
     * @throws RequestException
     */
    public function trackByShipmentId(int|string $shipmentId): array
    {
        return $this->request()
            ->get('courier/track/shipment/'.urlencode((string) $shipmentId))
            ->throw()
            ->json();
    }

    /**
     * @throws RequestException
     */
    public function cancelShipment(int|string $shipmentId): array
    {
        return $this->request()
            ->post('orders/cancel', [
                'ids' => [(int) $shipmentId],
            ])
            ->throw()
            ->json();
    }

    /**
     * @throws RequestException
     */
    public function generateLabel(array $shipmentIds): array
    {
        return $this->request()
            ->post('courier/generate/label', [
                'shipment_id' => $shipmentIds,
            ])
            ->throw()
            ->json();
    }

    /**
     * Check courier serviceability between pincodes.
     *
     * @throws RequestException
     */
    public function checkServiceability(array $query): array
    {
        return $this->request()
            ->get('courier/serviceability/', $query)
            ->throw()
            ->json();
    }

    protected function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->retry(2, 200)
            ->withToken($this->getToken());
    }

    protected function getToken(): string
    {
        if (! $this->email || ! $this->password) {
            throw new RuntimeException('Shiprocket credentials are not configured.');
        }

        $cacheKey = $this->cacheKey();

        return Cache::remember($cacheKey, now()->addMinutes($this->tokenTtlMinutes - 1), function () {
            $response = Http::baseUrl($this->baseUrl)
                ->acceptJson()
                ->post('auth/login', [
                    'email' => $this->email,
                    'password' => $this->password,
                ])
                ->throw()
                ->json();

            $token = $response['token'] ?? null;

            if (! $token) {
                throw new RuntimeException('Shiprocket authentication failed: token missing.');
            }

            return $token;
        });
    }

    protected function cacheKey(): string
    {
        return 'shiprocket:token:'.sha1($this->email.'|'.$this->baseUrl);
    }
}
