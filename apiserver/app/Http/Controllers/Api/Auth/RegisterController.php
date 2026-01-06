<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Casts\UserStatusCast;
use App\Helpers\OtpManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmailRegisterRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Services\IntegrationServices\Sms\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

final class RegisterController extends Controller
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
     * Register a new user with OTP verification
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Verify OTP first
            $otpValid = $this->otpManager->verify(
                $request->input('mobile'),
                $request->input('otp')
            );

            if (! $otpValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP',
                    'errors' => [
                        'otp' => ['Invalid or expired OTP'],
                    ],
                ], 422);
            }

            // Clear OTP after successful verification
            $this->otpManager->clear($request->input('mobile'));

            // Find parent user if referral code provided
            $parentUser = null;
            if ($request->filled('referral_code')) {
                $parentUser = User::where('referral_code', $request->input('referral_code'))->first();
            }

            // Create user
            $user = User::create([
                'name' => $request->input('name'),
                'mobile' => $request->input('mobile'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'mobile_verified_at' => now(),
                'parent_id' => $parentUser?->id,
                'status' => UserStatusCast::ACTIVE->value,
            ]);

            // Create Sanctum token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Send welcome notifications (async via queue)
            $this->sendWelcomeNotifications($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'uuid' => $user->uuid,
                        'name' => $user->name,
                        'mobile' => $user->mobile,
                        'email' => $user->email,
                        'type' => $user->type,
                        'status' => $user->status,
                        'referral_code' => $user->referral_code,
                    ],
                    'token' => $token,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Register a new user with email OTP verification
     */
    public function registerWithEmail(EmailRegisterRequest $request): JsonResponse
    {
        try {
            // Verify OTP first
            $otpValid = $this->otpManager->verify(
                $request->input('email'),
                $request->input('otp')
            );

            if (! $otpValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP',
                    'errors' => [
                        'otp' => ['Invalid or expired OTP'],
                    ],
                ], 422);
            }

            // Clear OTP after successful verification
            $this->otpManager->clear($request->input('email'));

            // Find parent user if referral code provided
            $parentUser = null;
            if ($request->filled('referral_code')) {
                $parentUser = User::where('referral_code', $request->input('referral_code'))->first();
            }

            // Create user
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
                'password' => Hash::make($request->input('password')),
                'email_verified_at' => now(),
                'parent_id' => $parentUser?->id,
                'status' => UserStatusCast::ACTIVE->value,
            ]);

            // Create Sanctum token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Send welcome notifications (async via queue)
            $this->sendWelcomeNotifications($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'uuid' => $user->uuid,
                        'name' => $user->name,
                        'mobile' => $user->mobile,
                        'email' => $user->email,
                        'type' => $user->type,
                        'status' => $user->status,
                        'referral_code' => $user->referral_code,
                    ],
                    'token' => $token,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send welcome notifications to newly registered user
     * - Email welcome (if email provided)
     * - SMS welcome
     * - Database notification (for in-app)
     */
    private function sendWelcomeNotifications(User $user): void
    {
        // Send welcome notification (email + database + push if subscribed)
        // This runs via queue for non-blocking response
        $user->notify(new WelcomeNotification(
            sendEmail: ! empty($user->email),
            sendPush: true,
        ));

        // Send welcome SMS
        if ($user->mobile) {
            app(SmsService::class)->sendWelcome($user->mobile, $user->name);
        }
    }
}
