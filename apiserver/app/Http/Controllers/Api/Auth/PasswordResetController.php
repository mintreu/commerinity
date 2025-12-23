<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\OtpManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class PasswordResetController extends Controller
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
     * Send password reset link via email
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $email = $request->input('email');
            $user = User::where('email', $email)->first();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not found',
                ], 404);
            }

            // Generate reset token
            $token = Str::random(64);

            // Store token in password_reset_tokens table
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'email' => $email,
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            // TODO: Send email with reset link in production

            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email',
                'demo' => config('app.env') !== 'production',
                'token' => config('app.env') !== 'production' ? $token : null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset link: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send password reset OTP via mobile
     */
    public function forgotPasswordMobile(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $mobile = $request->input('mobile');
            $user = User::where('mobile', $mobile)->first();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mobile number not found',
                ], 404);
            }

            // Generate and send OTP
            $otp = $this->otpManager->generate($mobile);

            // TODO: Send actual SMS in production

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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset password using email token
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $email = $request->input('email');
            $token = $request->input('token');

            // Find user
            $user = User::where('email', $email)->first();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Verify token
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $email)
                ->first();

            if (! $resetRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset token',
                ], 422);
            }

            if (! Hash::check($token, $resetRecord->token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset token',
                ], 422);
            }

            // Check token expiry (60 minutes)
            if ($resetRecord->created_at < now()->subMinutes(60)) {
                DB::table('password_reset_tokens')->where('email', $email)->delete();

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset token',
                ], 422);
            }

            // Update password
            $user->password = Hash::make($request->input('password'));
            $user->save();

            // Delete reset token
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            // Revoke all tokens (logout from all devices for security)
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password reset failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset password using mobile OTP
     */
    public function resetPasswordMobile(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $mobile = $request->input('mobile');
            $otp = $request->input('otp');

            // Find user
            $user = User::where('mobile', $mobile)->first();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Verify OTP
            $otpValid = $this->otpManager->verify($mobile, $otp);

            if (! $otpValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP',
                    'errors' => [
                        'otp' => ['Invalid or expired OTP'],
                    ],
                ], 422);
            }

            // Clear OTP
            $this->otpManager->clear($mobile);

            // Update password
            $user->password = Hash::make($request->input('password'));
            $user->save();

            // Revoke all tokens (logout from all devices for security)
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => [
                    'otp' => [$e->getMessage()],
                ],
            ], $e->getCode() ?: 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password reset failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
