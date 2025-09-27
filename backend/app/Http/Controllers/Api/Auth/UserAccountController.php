<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\OtpManager;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserIndexResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class UserAccountController extends Controller
{






    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $user->clearMediaCollection('avatarImage');
        $user->addMediaFromRequest('avatar')->toMediaCollection('avatarImage');

        return response()->json([
            'message' => 'Avatar updated successfully',
            'avatar' => $user->getAvatarAttribute(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'   => 'required|string|min:3|max:255',
            'gender' => 'nullable|string',
            'dob'    => 'nullable|date',
            'bio'    => 'nullable|string|max:500',
        ]);

        try {
            $user->update($validated);
            return response()->json(['message' => 'Profile updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update profile'], 500);
        }
    }


    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        // Validate input
        $request->validate([
            'current_password' => 'required|string|min:6',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Your current password is incorrect.'
            ], 422); // 422 to work well with useSanctumForm
        }

        // Update password securely
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
            'user'    => UserIndexResource::make($user->fresh()),
        ]);
    }




    public function updateContact(Request $request)
    {
        $validated = $request->validate([
            'type'   => 'required|in:email,mobile',
            'email'  => 'nullable|required_if:type,email|email',
            'mobile' => 'nullable|required_if:type,mobile|string|max:10',
            'otp'    => 'required|string',
        ]);


        $credential = $validated[$validated['type']];

        $isValid = OtpManager::make()->validateDemoOtp($credential,$validated['otp']);

        if ($isValid) {
            $user = $request->user();
            $user->update([
                $validated['type']             => $credential,
                $validated['type'].'_verified_at' => now(),
            ]);

            OtpManager::make()->destroyOtp($credential, true);

            return response()->json([
                'success' => true,
                'message' => ucfirst($validated['type']) . ' updated successfully.',
                'user'    => UserIndexResource::make($user->fresh()),
            ]);
        }

        return response()->json([
            'message' => 'Invalid OTP.',
        ], 422);



    }




    /**
     * Export user data to email
     */
    public function exportData(Request $request)
    {
        $user = $request->user();

        // Check if user has verified email
        if (!$user->email || !$user->email_verified_at) {
            return response()->json([
                'message' => 'A verified email address is required to export your data.'
            ], 422);
        }

        try {
            // Dispatch job to generate and email the data export
            ExportUserDataJob::dispatch($user);

            return response()->json([
                'success' => true,
                'message' => 'Data export initiated successfully. You will receive an email with your data within 5-10 minutes.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to initiate data export. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete user account with OTP verification
     */
    public function deleteAccount(Request $request)
    {
        $validated = $request->validate([
            'mobile' => 'required|string|max:10',
            'otp'    => 'required|string|size:6',
        ]);

        $user = $request->user();

        // Verify mobile number matches
        if ($user->mobile !== $validated['mobile']) {
            return response()->json([
                'message' => 'Mobile number does not match your account.'
            ], 422);
        }

        // Validate OTP
        $isValid = OtpManager::make()->validateDemoOtp($validated['mobile'], $validated['otp']);

        if (!$isValid) {
            return response()->json([
                'message' => 'Invalid OTP. Please try again.'
            ], 422);
        }

        try {
            // Clean up OTP
            OtpManager::make()->destroyOtp($validated['mobile'], true);

            // Log the deletion for compliance
             Log::info('Account deletion initiated', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_mobile' => $user->mobile,
                'deleted_at' => now(),
            ]);

            // Delete user account
            // Note: Consider soft deletion for compliance requirements
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Your account has been permanently deleted.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete account. Please try again.'
            ], 500);
        }
    }


}
