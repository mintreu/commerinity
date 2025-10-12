<?php

namespace App\Helpers;

use App\Mail\OtpMail;
use App\Models\Distributor;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Mintreu\LaravelIntegration\LaravelIntegration;
use Random\RandomException;

class OtpManager
{

    // Define constants for credential types
    const MOBILE = 'mobile';
    const EMAIL = 'email';




    public static function make(): static
    {
        return new static();
    }


// ==========================
    // ✅ NEW DEMO METHODS
    // ==========================

    /**
     * Generate and store a demo OTP (fixed or random for testing).
     *
     * @param string $credential
     * @return int Demo OTP (plain, not hashed)
     */
    public function generateDemoOtp(string $credential): int
    {
        $otp = 123456; // fixed demo OTP (you can randomize if needed)
        $hashedOtp = Hash::make($otp);

        // Store OTP in cache with short expiry (e.g., 5 min)
        Cache::put($this->getCacheKey('demo_' . $credential), $hashedOtp, now()->addMinutes(5));

        return $otp;
    }

    /**
     * Validate a demo OTP (no SMS/Email sending).
     *
     * @param string $credential
     * @param int $otp
     * @return array
     */
    public function validateDemoOtp(string $credential, int $otp): array
    {
        $cachedOtp = Cache::get($this->getCacheKey('demo_' . $credential));

        if (!$cachedOtp) {
            return [
                'status' => false,
                'msg' => 'Demo OTP Expired!'
            ];
        }

        return Hash::check($otp, $cachedOtp) ? [
            'status' => true,
            'msg' => 'Demo OTP successfully validated.'
        ] : [
            'status' => false,
            'msg' => 'Incorrect Demo OTP.'
        ];
    }

    // ==========================
    // EXISTING METHODS (unchanged)
    // ==========================



    /**
     * Send OTP to the given credential (phone or email).
     *
     * @param string $credential
     * @param string $credentialType - 'phone' or 'email'
     * @return bool
     * @throws RandomException
     */
    public function sendOtp(string $credential, string $credentialType): bool
    {

        // Ensure credentialType is valid
        if (!in_array($credentialType, [self::MOBILE, self::EMAIL])) {
            return false; // Invalid credential type
        }

        $otp = $this->generateOtp();
        $hashedOtp = Hash::make($otp); // Securely hash OTP

        // Store OTP in cache with a 10-minute expiration time
        Cache::put($this->getCacheKey($credential), $hashedOtp, now()->addMinutes(10));

        // Construct the OTP message
        $message = config('app.name') . "\n" .
            'Don\'t share this code.' . "\n" .
            'Your OTP: ' . $otp . "\n" .
            'This OTP is valid for 10 minutes.';

        if ($credentialType === self::MOBILE) {
            return $this->sendOtpViaSms($credential, $message);
        } elseif ($credentialType === self::EMAIL) {
            return $this->sendOtpViaEmail($credential, $otp);
        }

        return false;
    }

    /**
     * Validate the OTP for the given credential (phone or email).
     *
     * @param string $credential
     * @param int $otp
     * @return array
     */
    public function validateOtp(string $credential, int $otp): array
    {
        $cachedOtp = Cache::get($this->getCacheKey($credential));

        // If cache is empty or token does not match, return false
        if (!$cachedOtp) {
            return [
                'status' => false,
                'msg' => 'OTP Expired!'
            ];
        }

        // Validate the token by comparing hashed values
        return Hash::check($otp, $cachedOtp) ? [
            'status' => true,
            'msg' => 'Your OTP has been successfully validated.'
        ] : [
            'status' => false,
            'msg' => 'The OTP you entered is incorrect. Please try again.'
        ];
    }

    /**
     * Generate a secure 6-digit OTP.
     *
     * @return int
     * @throws RandomException
     */
    private function generateOtp(): int
    {
        return random_int(100000, 999999); // 6-digit random OTP
    }

    /**
     * Generate a unique cache key for the given credential.
     *
     * @param string $credential
     * @return string
     */
    private function getCacheKey(string $credential): string
    {
        return 'otp_' . md5($credential); // Unique cache key per credential
    }


    /**
     * Destroy (invalidate) the current OTP for a credential.
     *
     * @param string $credential
     * @param bool $isDemo
     * @return bool
     */
    public function destroyOtp(string $credential, bool $isDemo = false): bool
    {
        $key = $this->getCacheKey(($isDemo ? 'demo_' : '') . $credential);

        if (Cache::has($key)) {
            Cache::forget($key);
            return true;
        }

        return false; // No OTP found to destroy
    }


    /**
     * Send OTP via SMS (using an existing SMS service like Fast2Sms).
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    private function sendOtpViaSms(string $phoneNumber, string $message): bool
    {
        // Call the SMS service to send the OTP (example using Fast2Sms)
        $smsService = LaravelIntegration::sms();
        $response = $smsService->send([$phoneNumber], $message);
        return $response['return'] ?? false;
    }

    /**
     * Send OTP via Email using Notification.
     *
     * @param string $email
     * @param int $otp
     * @return bool
     */
    private function sendOtpViaEmail(string $email, int $otp): bool
    {
        // Try to find existing user or distributor
        $existUser = User::firstWhere('email', $email)
            ?? Distributor::firstWhere('email', $email);

        if ($existUser) {
            // ✅ Existing user or distributor — use Laravel Notification
            $existUser->notify(new \App\Notifications\OtpNotification($otp));
        } else {
            // ✅ No existing user — send mail directly (for registration)
            Mail::to($email)->send(new OtpMail($otp, 'registration'));
        }

        return true;
    }

}
