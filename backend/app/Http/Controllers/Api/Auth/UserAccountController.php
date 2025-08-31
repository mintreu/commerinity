<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\OtpManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'message' => 'Password updated successfully.'
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
                'message' => ucfirst($validated['type']) . ' updated successfully.',
                'user'    => $user->fresh(),
            ]);
        }

        return response()->json([
            'message' => 'Invalid OTP.',
        ], 422);



    }







}
