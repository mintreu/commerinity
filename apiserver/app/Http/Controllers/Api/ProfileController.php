<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class ProfileController extends Controller
{
    /**
     * Update the authenticated user's profile.
     *
     * Email and mobile changes require verification before taking effect.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Separate fields that need verification
        $pendingVerification = [];
        $directUpdate = [];

        // Name is always directly updated
        $directUpdate['name'] = $validated['name'];

        // Bio can be directly updated (nullable)
        if (array_key_exists('bio', $validated)) {
            $directUpdate['bio'] = $validated['bio'];
        }

        // Gender - use existing value if not provided or empty
        if (array_key_exists('gender', $validated) && ! empty($validated['gender'])) {
            $directUpdate['gender'] = $validated['gender'];
        }

        // DOB can be directly updated (nullable)
        if (array_key_exists('dob', $validated)) {
            $directUpdate['dob'] = $validated['dob'];
        }

        // Email change requires verification
        if (array_key_exists('email', $validated) && $validated['email'] !== $user->email) {
            if (! empty($validated['email'])) {
                $pendingVerification['email'] = $validated['email'];
            }
        }

        // Mobile change requires verification
        if (array_key_exists('mobile', $validated) && $validated['mobile'] !== $user->mobile) {
            if (! empty($validated['mobile'])) {
                $pendingVerification['mobile'] = $validated['mobile'];
            }
        }

        // Apply direct updates
        $user->update($directUpdate);

        // Build response
        $response = [
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => new UserResource($user->fresh()),
            ],
        ];

        // If there are pending verification items, notify the user
        if (! empty($pendingVerification)) {
            $pendingItems = [];

            if (isset($pendingVerification['email'])) {
                $pendingItems[] = 'email';
                // TODO: Send verification email to new address
                // $user->sendEmailChangeVerification($pendingVerification['email']);
            }

            if (isset($pendingVerification['mobile'])) {
                $pendingItems[] = 'mobile number';
                // TODO: Send OTP to new mobile
                // $user->sendMobileChangeOtp($pendingVerification['mobile']);
            }

            $response['message'] = 'Profile updated. Please verify your new '.implode(' and ', $pendingItems).' to complete the change.';
            $response['pending_verification'] = array_keys($pendingVerification);
        }

        return response()->json($response);
    }

    /**
     * Upload avatar for the authenticated user.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
<<<<<<< Updated upstream
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'], // 2MB max
=======
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'], // 2MB max
>>>>>>> Stashed changes
        ]);

        $user = $request->user();

<<<<<<< Updated upstream
        // Delete old avatar if exists
        if ($user->hasMedia('avatar')) {
            $user->clearMediaCollection('avatar');
        }

        // Add new avatar
=======
        // Clear existing avatar (singleFile collection will auto-replace)
>>>>>>> Stashed changes
        $user->addMediaFromRequest('avatar')
            ->toMediaCollection('avatar');

        return response()->json([
            'message' => 'Avatar uploaded successfully.',
            'data' => [
                'user' => new UserResource($user->fresh()),
            ],
        ]);
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Verify current password
        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors' => [
                    'current_password' => ['The current password is incorrect.'],
                ],
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Optionally revoke all other tokens except current
        if ($validated['logout_other_devices'] ?? false) {
            $currentToken = $user->currentAccessToken();
            if ($currentToken && ! $currentToken instanceof \Laravel\Sanctum\TransientToken) {
                $user->tokens()->where('id', '!=', $currentToken->id)->delete();
            }
        }

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }
}
