<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\OtpManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final class OtpController extends Controller
{
    private readonly OtpManager $otpManager;

    public function __construct()
    {
        $this->otpManager = new OtpManager(
            cache()->store(),
            app('hash'),
            (bool) config('services.sms.options.demo_mode', false)
        );
    }

    /**
     * Send OTP to mobile or email
     */
    public function send(SendOtpRequest $request): JsonResponse
    {
        try {
            $credential = $request->input('value');
            $type = $request->input('type');

            $result = $this->otpManager->sendOtp($credential, $type);

            return response()->json([
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'OTP send failed',
                'demo' => $result['demo'] ?? false,
                'otp' => $result['otp'] ?? null,
            ], ($result['success'] ?? false) ? 200 : (int) ($result['code'] ?? 422));
        } catch (\RuntimeException $e) {
            Log::warning('OTP send failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 422);
        }
    }

    /**
     * Verify OTP
     */
    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $credential = $request->input('value');
            $otp = $request->input('otp');

            $valid = $this->otpManager->verify($credential, $otp);

            if ($valid) {
                // Clear OTP after successful verification
                $this->otpManager->clear($credential);

                return response()->json([
                    'success' => true,
                    'valid' => true,
                    'message' => 'OTP verified successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'OTP expired or invalid',
            ], 422);
        } catch (\RuntimeException $e) {
            Log::warning('OTP verify failed', ['error' => $e->getMessage()]);
            $status = $e->getCode() === 429 ? 429 : 422;
            $message = $status === 429
                ? 'Too many attempts. Please request a new OTP.'
                : 'Something went wrong. Please try again.';

            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => $message,
            ], $status);
        }
    }
}
