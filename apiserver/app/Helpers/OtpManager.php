<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\User;
use App\Notifications\OtpNotification;
use App\Services\IntegrationServices\Sms\SmsService;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

final class OtpManager implements \App\Contracts\Helpers\OtpManagerInterface
{
    public const CREDENTIAL_MOBILE = 'mobile';

    public const CREDENTIAL_EMAIL = 'email';

    private const OTP_TTL_MINUTES = 10;

    private const OTP_MIN = 100000;

    private const OTP_MAX = 999999;

    private const DEMO_OTP = 123456;

    private const MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly CacheContract $cache,
        private readonly Hasher $hasher,
        private readonly bool $isDemoMode = false
    ) {}

    /**
     * Generate OTP without sending (for internal use)
     */
    public function generate(string $credential): int
    {
        $this->validateCredential($credential);
        $this->enforceRateLimit($credential);

        if ($this->isDemoMode) {
            return $this->generateDemo($credential);
        }

        $otp = random_int(self::OTP_MIN, self::OTP_MAX);
        $this->store($credential, $otp);

        return $otp;
    }

    /**
     * Generate and send OTP via appropriate channel (SMS or Email)
     *
     * @param  string  $credential  Email or mobile number
     * @param  string  $credentialType  'mobile' or 'email'
     * @param  string  $purpose  Purpose of OTP (registration, login, password_reset)
     * @return array{success: bool, otp?: int, message: string}
     */
    public function sendOtp(string $credential, string $credentialType, string $purpose = 'verification'): array
    {
        if (! in_array($credentialType, [self::CREDENTIAL_MOBILE, self::CREDENTIAL_EMAIL])) {
            return ['success' => false, 'message' => 'Invalid credential type'];
        }

        try {
            $otp = $this->generate($credential);

            if ($credentialType === self::CREDENTIAL_MOBILE) {
                $result = $this->sendOtpViaSms($credential, (string) $otp);
            } else {
                $result = $this->sendOtpViaEmail($credential, (string) $otp, $purpose);
            }

            if ($result) {
                return [
                    'success' => true,
                    'otp' => $this->isDemoMode ? $otp : null, // Only return OTP in demo mode
                    'message' => 'OTP sent successfully',
                ];
            }

            return ['success' => false, 'message' => 'Failed to send OTP'];

        } catch (RuntimeException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send OTP via SMS
     */
    private function sendOtpViaSms(string $phone, string $otp): bool
    {
        $smsService = app(SmsService::class);
        $result = $smsService->sendOtp($phone, $otp);

        if (! $result['success']) {
            Log::warning('SMS OTP delivery failed', [
                'phone' => $this->maskCredential($phone),
                'error' => $result['message'] ?? 'Unknown error',
            ]);
        }

        return $result['success'];
    }

    /**
     * Send OTP via Email
     */
    private function sendOtpViaEmail(string $email, string $otp, string $purpose): bool
    {
        try {
            // Check if user exists
            $user = User::where('email', $email)->first();

            if ($user) {
                // Use notification for existing users
                $user->notify(new OtpNotification($otp, $purpose));
            } else {
                // For registration, send via direct mail
                Mail::to($email)->send(new \App\Mail\OtpMail($otp, $purpose));
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Email OTP delivery failed', [
                'email' => $this->maskCredential($email),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function verify(string $credential, string $otp): bool
    {
        $this->validateCredential($credential);
        $this->incrementAttempts($credential);

        $stored = $this->retrieve($credential);

        if (! $stored) {
            $this->handleFailedAttempt($credential);

            return false;
        }

        if (! $this->hasher->check($otp, $stored)) {
            $this->handleFailedAttempt($credential);

            return false;
        }

        $this->clearAttempts($credential);

        return true;
    }

    public function clear(string $credential): void
    {
        $this->cache->forget($this->getCacheKey($credential));
        $this->clearAttempts($credential);
    }

    private function store(string $credential, int $otp): void
    {
        $key = $this->getCacheKey($credential);
        $hashedOtp = $this->hasher->make((string) $otp);

        $this->cache->put($key, $hashedOtp, now()->addMinutes(self::OTP_TTL_MINUTES));
    }

    private function retrieve(string $credential): ?string
    {
        $key = $this->getCacheKey($credential);

        return $this->cache->get($key);
    }

    private function generateDemo(string $credential): int
    {
        $this->store($credential, self::DEMO_OTP);

        Log::channel('single')->info('Demo OTP generated', [
            'credential' => $this->maskCredential($credential),
            'otp' => self::DEMO_OTP,
        ]);

        return self::DEMO_OTP;
    }

    private function enforceRateLimit(string $credential): void
    {
        $key = "otp_rate_limit:{$credential}";
        $attempts = (int) $this->cache->get($key, 0);

        if ($attempts >= 3) {
            throw new RuntimeException('Too many OTP requests. Please try again in 15 minutes.', 429);
        }

        $this->cache->put($key, $attempts + 1, now()->addMinutes(15));
    }

    private function incrementAttempts(string $credential): void
    {
        $key = "otp_attempts:{$credential}";
        $attempts = (int) $this->cache->get($key, 0);

        $this->cache->put($key, $attempts + 1, now()->addMinutes(30));
    }

    private function handleFailedAttempt(string $credential): void
    {
        $key = "otp_attempts:{$credential}";
        $attempts = (int) $this->cache->get($key, 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            // Clear only the OTP, keep attempts counter for rate limiting
            $this->cache->forget($this->getCacheKey($credential));

            if ($attempts > self::MAX_ATTEMPTS) {
                throw new RuntimeException('Maximum OTP attempts exceeded. Please request a new OTP.', 429);
            }
        }
    }

    private function clearAttempts(string $credential): void
    {
        $this->cache->forget("otp_attempts:{$credential}");
    }

    private function getCacheKey(string $credential): string
    {
        return 'otp:'.hash('xxh3', $credential);
    }

    private function validateCredential(string $credential): void
    {
        if (empty(trim($credential))) {
            throw new RuntimeException('Credential cannot be empty', 422);
        }
    }

    private function maskCredential(string $credential): string
    {
        if (str_contains($credential, '@')) {
            [$local, $domain] = explode('@', $credential);

            return substr($local, 0, 2).'***@'.$domain;
        }

        return substr($credential, 0, 3).'***'.substr($credential, -2);
    }

    /**
     * Check if OTP exists for credential
     */
    public function exists(string $credential): bool
    {
        return $this->retrieve($credential) !== null;
    }

    /**
     * Get remaining attempts for credential
     */
    public function getRemainingAttempts(string $credential): int
    {
        $key = "otp_attempts:{$credential}";
        $attempts = (int) $this->cache->get($key, 0);

        return max(0, self::MAX_ATTEMPTS - $attempts);
    }

    /**
     * Get cooldown seconds until next OTP can be sent
     */
    public function getCooldownSeconds(string $credential): int
    {
        $key = "otp_rate_limit:{$credential}";
        $attempts = (int) $this->cache->get($key, 0);

        if ($attempts >= 3) {
            // Estimate remaining time - check TTL in cache
            // Cache::ttl() isn't available for all drivers, so estimate from creation
            return 15 * 60; // 15 minutes max
        }

        return 0;
    }

    /**
     * Check if in demo mode
     */
    public function isDemoMode(): bool
    {
        return $this->isDemoMode;
    }
}
