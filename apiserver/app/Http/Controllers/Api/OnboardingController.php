<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\OtpManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\OnboardingProfileRequest;
use App\Services\OnboardingVerifierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class OnboardingController extends Controller
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
     * Get current onboarding status with progress
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $verifier = OnboardingVerifierService::for($user);

        return response()->json($verifier->getFullSummary());
    }

    /**
     * Update profile information (Step 1)
     */
    public function updateProfile(OnboardingProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update($request->validated());

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Verify and add email/mobile during onboarding (Step 2)
     */
    public function verifyContact(Request $request): JsonResponse
    {
        $user = $request->user();

        $rules = [
            'type' => ['required', 'in:email,mobile'],
            'value' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
        ];

        $type = $request->input('type');
        if ($type === 'email') {
            $rules['value'][] = 'email';
            $rules['value'][] = Rule::unique('users', 'email')->ignore($user?->id);
        } else {
            $rules['value'][] = 'regex:/^\+[1-9]\d{1,14}$/';
            $rules['value'][] = Rule::unique('users', 'mobile')->ignore($user?->id);
        }

        $request->validate($rules, [
            'value.regex' => 'Mobile number must be in E.164 format (e.g., +919876543210).',
        ]);

        $value = $request->input('value');
        $otp = $request->input('otp');

        if ($type === 'email' && $user->email === $value && $user->email_verified_at) {
            return response()->json([
                'message' => 'Email verified and added successfully',
                'user' => $user->fresh(),
            ]);
        }

        if ($type === 'mobile' && $user->mobile === $value && $user->mobile_verified_at) {
            return response()->json([
                'message' => 'Mobile verified and added successfully',
                'user' => $user->fresh(),
            ]);
        }

        // Verify OTP
        if (! $this->otpManager->verify($value, $otp)) {
            return response()->json([
                'message' => 'Invalid or expired OTP',
            ], 422);
        }

        // Clear OTP
        $this->otpManager->clear($value);

        // Update user
        if ($type === 'email') {
            $user->update([
                'email' => $value,
                'email_verified_at' => now(),
            ]);
        } else {
            $user->update([
                'mobile' => $value,
                'mobile_verified_at' => now(),
            ]);
        }

        return response()->json([
            'message' => ucfirst($type).' verified and added successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Mark onboarding as complete
     */
    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();
        $verifier = OnboardingVerifierService::for($user);

        // Validate minimum requirements
        if (! $verifier->canCompleteOnboarding()) {
            return response()->json([
                'message' => 'Please complete all required steps before finishing onboarding',
                'missing' => $verifier->getMissingRequiredSteps(),
            ], 422);
        }

        $user->update(['onboarded' => true]);

        return response()->json([
            'message' => 'Onboarding completed successfully! Welcome aboard!',
            'user' => $user->fresh(),
        ]);
    }
}
