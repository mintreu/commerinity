<?php

namespace App\Services\ProviderServices\PayoutService\Providers\LaravelEaseBuzz\Support;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

trait HasEaseBuzzRequest
{


    protected function makeRequest(string $endpoint, array $data): array
    {
        try {

            $response = new Client();


            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => '',
                'Content-Type' => 'application/json',
                'WIRE-API-KEY' => $this->apiKey,
            ])->post("{$this->baseUrl}{$endpoint}", $data);

            $response->throw(); // Ensure the request throws an exception for non-2xx responses.

            return $response->json();
        } catch (\Exception $e) {
            Log::error("Easebuzz API Error: {$e->getMessage()}", [
                'endpoint' => $endpoint,
                'data' => $data,
            ]);

            throw $e; // Optionally rethrow for further handling.
        }
    }



}
