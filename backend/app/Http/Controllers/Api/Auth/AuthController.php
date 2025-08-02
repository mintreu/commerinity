<?php

namespace App\Http\Controllers\Api\Auth;

use App\Casts\AuthTypeCast;
use App\Casts\ModelStatusCast;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{


    /**
     * @throws ValidationException
     */
    public function storeToken(Request $request)
    {

        $request->validate([
           'email' => 'required|email',
           'password' => 'required'
        ]);

        $user = User::firstWhere('email',$request->email);

        if (!$user || !Hash::check($request->password,$user->password))
        {
            throw ValidationException::withMessages([
               'email' => ['The provided credentials are incorrect.']
            ]);
        }

        return ['token' => $user->createToken('token-name')->plainTextToken];

    }


    public function destroyToken(Request $request)
    {
        // Revoke All Tokens
        // $request->user()->tokens()->delete();

        // Revoke only current token which used for authenticate
        $request->user()->currentAccessToken()->delete();
    }


    public function checkContactExistence(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:email,mobile',
            'value' => 'required|string',
        ]);

        $user = User::where($request->type, $request->value)->first();

        return response()->json([
            'data' => [
                'exists' => $user ? true : false,
            ]
        ]);

    }


    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:email,mobile',
            'value' => 'required|string',
        ]);

        $type = $request->type;
        $value = $request->value;

        // You can replace this logic to send SMS or email
        $otp = '123456';

        // Store in cache for 5 minutes
        Cache::put("otp_{$type}_{$value}", $otp, now()->addMinutes(5));

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully',
            "note" => 'Otp will be expire after 5 minutes from now'
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:email,mobile',
            'value' => 'required|string',
            'otp'   => 'required|digits:6',
        ]);

        $type = $request->type;
        $value = $request->value;
        $inputOtp = $request->otp;

        $cachedOtp = Cache::get("otp_{$type}_{$value}");

        if (!$cachedOtp || $cachedOtp !== $inputOtp) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired OTP',
            ], 422);
        }

        return response()->json([
            'data' => [
                'valid' => true,
                'message' => 'OTP verified successfully',
            ]
        ]);
//        return response()->json([
//            'valid' => true,
//            'message' => 'OTP verified successfully',
//        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'bail|nullable|email|unique:users,email',
            'mobile'  => 'bail|nullable|digits:10|unique:users,mobile',
            'gender'   => 'required|in:male,female,other',
            'dob'      => 'required|date|before:today',
            'password' => 'required|string|min:6',
            'type'     => 'required|in:email,mobile',
            'referral' => 'nullable|string|unique:users,referral_code',
            'otp'       => 'nullable|string'
        ]);

        $email = $request->type === 'email'  ? $request->email   : null;
        $contact = $request->type === 'mobile' ? $request->mobile : null;

        $validateField = [];
        if ($request->otp)
        {
            $validateField = [
                $request->type.'_verified_at' => now()
            ];
        }



        $credential = array_merge([
            'name'     => $request->name,
            'email'    => $email,
            'mobile'  => $contact,
            'gender'   => $request->gender,
            'dob'      => $request->dob,
            'password' => bcrypt($request->password),
            'type'      => AuthTypeCast::REGULAR,
            'status'    => ModelStatusCast::DRAFT,
        ],$validateField);


        if ($request->referral)
        {
            $parentUser = User::firstWhere('referral_code',$request->referral);
            $user = $parentUser->children()->create($credential);
        }else{
            $user = User::create($credential);
        }




        return response()->json([
            'status'  => 'success',
            'message' => 'Registration complete',
            'user'    => UserResource::make($user),
        ]);
    }



    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        // Step 1: Validate request input (basic shape)
        $validated = $request->validate([
            'email'         => ['bail', 'nullable', 'email','exists:users,email'],
            'mobile'        => ['bail', 'nullable', 'string','exists:users,mobile'],
            'password'      => ['bail', 'nullable', 'string'],
            'validated_otp' => ['bail', 'nullable', 'boolean'],
            'remember'      => ['bail', 'sometimes', 'boolean'],
        ]);

        if (empty($validated['email']) && empty($validated['mobile'])) {
            throw ValidationException::withMessages([
                'email_or_mobile' => ['Either email or mobile number is required.'],
            ]);
        }

        $method = !isset($validated['email']) ? 'mobile': 'email';
        $identifier = $validated[$method];

        // Email Case
        if ($method == 'email' && !isset($validated['password']))
        {
            throw ValidationException::withMessages([
                'password' => ['Password is required for email login.'],
            ]);
        }


        // Mobile Case With Password
        if ($method == 'mobile' && !isset($validated['validated_otp']) && !isset($validated['password']))
        {
            throw ValidationException::withMessages([
                'validated_otp_or_password' => ['Either otp verification or password is required.'],
            ]);
        }
        if (!isset($validated['validated_otp']))
        {
            $credentials = [
              $method => $identifier,
              'password' => $validated['password'],
            ];
            if (!Auth::attempt($credentials, $validated['remember'])) {
                throw ValidationException::withMessages([
                    $method => ['The provided credentials are incorrect.'],
                ]);
            }
        }else{
            $user = User::firstWhere($method,$identifier);

            if (!$user)
            {
                throw ValidationException::withMessages([
                    $method => ['The provided credentials are incorrect.'],
                ]);
            }

            Auth::login($user,$validated['remember']);
            if (! Auth::check()) {
                throw ValidationException::withMessages([
                    $method => ['Unable to log you in.'],
                ]);
            }

        }



        // Step 6: Success — regenerate session
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful',
            'data'    => UserResource::make(auth()->user()),
        ]);
    }


    /**
     * Log the user out and invalidate the session.
     */
    public function logout(Request $request)
    {
        if(Auth::guard('web')->check())
        {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Logout successful',
            ]);
        }
        return response()->json([
            'message' => 'No user found! make sure you login your account and then try to logout',
        ]);
    }




//    Profile Related


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




}
