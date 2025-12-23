<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\OtpManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use Illuminate\Http\JsonResponse;

final class OtpController extends Controller
{
    private readonly OtpManager $otpManager;

    public function __construct()
    {
        $this->otpManager = new OtpManager(
            cache()->store(),
            app('hash'),
            config('app.env') !== 'production'
        );
    }

    /**
     * Send OTP to mobile or email
     */
    public function send(SendOtpRequest $request): JsonResponse
    {
        try {
            $credential = $request->input('value');

            $otp = $this->otpManager->generate($credential);

            // TODO: Send actual SMS/Email in production
            // For now, return OTP in demo mode

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'demo' => config('app.env') !== 'production',
                'otp' => config('app.env') !== 'production' ? $otp : null,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
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
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }
}
