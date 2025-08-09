<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserIndexResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class SanctumUserController extends Controller
{



    public function getUser(Request $request)
    {
       // return $request->user();
        return UserIndexResource::make($request->user());
    }

    public function getProfile(Request $request)
    {
        $user = $request->user();
        $user->load([

        ]);
    }



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
            'name'  => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json(['message' => 'Profile updated']);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Invalid current password'], 403);
        }

        $user->update(['password' => $request->new_password]);

        return response()->json(['message' => 'Password updated']);
    }




    public function processOnboarding(Request $request)
    {
//        $validated = $request->validate([]);

        $loggedInUser = $request->user();


        if ($loggedInUser->email != $request->get('email') || $loggedInUser->mobile != $request->get('mobile'))
        {
            return response()->json([
                'data' => [
                    'status' => false,
                    'error' => 'credential not matched with active user'
                ]
            ]);
        }

        if (!$request->get('tnc'))
        {
            return response()->json([
                'data' => [
                    'status' => false,
                    'error' => 'user must agree with our terms and conditions for complete onboarding process'
                ]
            ]);
        }

        $addressFillables = [
            'postal_code'   => $request->get('postal_code'),
            'state_code'    =>  $request->get('state_code'),
            'village'       => $request->get('village'),
            'block_id'      => $request->get('block_id'),
            'address_1'     => $request->get('address_1'),
        ];

        $existAddress = $loggedInUser->addresses()->hasPostal($request->get('postal_code'))->first();

        if (!$existAddress)
        {
            // new address
            dd('new address');
        }




        return response()->json([
            'data' => [
                'status' => true,
                'error' => 'user onbording complete'
            ]
        ]);

    }




}
