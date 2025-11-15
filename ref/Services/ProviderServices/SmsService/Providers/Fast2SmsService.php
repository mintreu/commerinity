<?php

namespace App\Services\ProviderServices\SmsService\Providers;

use App\Services\ProviderServices\SmsService\Support\SmsServiceContract;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Random\RandomException;




class Fast2SmsService implements SmsServiceContract
{

    protected const API_URL = "https://www.fast2sms.com/dev/bulkV2";
    protected const WALLET_API_URL = "https://www.fast2sms.com/dev/wallet";
    private array $data = [];
    private string $apiKey;
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->apiKey = config('services.sms.providers.fast2sms.key');
        $this->http = $http;
    }

    public static function make(): Fast2SmsService
    {
        return app(Fast2SmsService::class)->getInstance();
    }

    protected function getInstance(): static
    {
        return $this;
    }

    public function getName(): string
    {
        return 'fast2sms';
    }

    public function send(array $numbers, string $message, bool $flash = false, string $lang = 'english')
    {
        try {
            $this->data = [
                'message' => $message,
                'language' => $lang,
                'route' => 'q',
                'flash' => $flash,
                'numbers' => implode(',', $numbers), // Ensure numbers are properly formatted
            ];

            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post(self::API_URL, $this->data);

            return $response->json();
        } catch (\Throwable $t) {
            Log::error('SMS sending failed: ' . $t->getMessage());
            return false;
        }
    }

    public function getBalance(): ?string
    {
        try {
            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
            ])->post(self::WALLET_API_URL);

            $responseData = $response->json();

            if ($response->successful() && $responseData['return']) {
                return $responseData['wallet']; // Return the wallet balance
            } else {
                Log::error('Failed to retrieve wallet balance: ' . $response->body());
                return null;
            }
        } catch (\Throwable $t) {
            Log::error('Wallet balance check failed: ' . $t->getMessage());
            return null;
        }
    }

    /**
     * Send OTP to the given phone number.
     *
     * @param string $phoneNumber
     * @return bool
     * @throws RandomException
     */
    public function sendOtp(string $phoneNumber): bool
    {
        $token = random_int(100000, 999999);
        $hashedToken = Hash::make($token);

        // Store OTP in cache with a 10-minute expiration time
        Cache::put($this->getCacheKey($phoneNumber), $hashedToken, now()->addMinutes(10));

        // Construct the OTP message
        $message = config('app.name') . "\n" .
            'Don\'t Share This Code.' . "\n" .
            'Your OTP: ' . $token . "\n" .
            'This OTP is valid for 10 minutes.';

        $numbers = [$phoneNumber];
        $response = $this->send($numbers, $message);
        return $response['return'];
    }

    /**
     * Validate the given OTP for the phone number.
     *
     * @param string $phoneNumber
     * @param int $token
     * @return array
     */
    public function validateOtp(string $phoneNumber, int $token): array
    {
        $cachedToken = Cache::get($this->getCacheKey($phoneNumber));

        // If cache is empty or token does not match, return false
        if (!$cachedToken) {
            return [
                'status' => false,
                'msg' => 'OTP Expired!'
            ];
        }

        // Validate the token by comparing hashed values
        return Hash::check($token, $cachedToken) ? [
            'status' => true,
            'msg' => 'Your OTP has been successfully validated.'
        ] : [
            'status' => false,
            'msg' => 'The OTP you entered is incorrect. Please try again.'
        ];
    }

    /**
     * Generate a unique cache key for the given phone number.
     *
     * @param string $phoneNumber
     * @return string
     */
    private function getCacheKey(string $phoneNumber): string
    {
        return 'otp_' . md5($phoneNumber);
    }
}
