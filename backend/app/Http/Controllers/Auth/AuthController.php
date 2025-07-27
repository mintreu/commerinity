<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
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
            'email'    => 'nullable|email|unique:users,email',
            'mobile'  => 'nullable|digits:10|unique:users,mobile',
            'gender'   => 'required|in:male,female,other',
            'dob'      => 'required|date|before:today',
            'password' => 'required|string|min:6',
            'type'     => 'required|in:email,mobile',
        ]);

        $email = $request->type === 'email'  ? $request->email   : null;
        $contact = $request->type === 'mobile' ? $request->mobile : null;

        $user = User::create([
            'name'     => $request->name,
            'email'    => $email,
            'mobile'  => $contact,
            'gender'   => $request->gender,
            'dob'      => $request->dob,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Registration complete',
            'user'    => $user,
        ]);
    }



    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        // Step 1: Validate the request
        $validated = $request->validate([
            'email'    => ['bail', 'nullable', 'email'],
            'mobile'   => ['bail', 'nullable', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        // Step 2: Determine login field
        $loginField = null;
        if (empty($validated['mobile']) && empty($validated['email']))
        {
            throw ValidationException::withMessages([
                'email_or_mobile' => ['Either email or mobile is required.'],
            ]);
        }
        $loginField = !empty($validated['mobile']) ? 'mobile' : 'email';

        // Step 3: Attempt login
        $credentials = [
            $loginField => $validated[$loginField],
            'password'  => $validated['password'],
        ];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                $loginField => ['The provided credentials are incorrect.'],
            ]);
        }

        // Step 4: Regenerate session & return response
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful',
            'data'    => auth()->user(),
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


}
