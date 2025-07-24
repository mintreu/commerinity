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
            'message' => 'OTP sent successfully'
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
            'contact'  => 'nullable|digits:10|unique:users,mobile',
            'gender'   => 'required|in:male,female,other',
            'dob'      => 'required|date|before:today',
            'password' => 'required|string|min:6',
            'type'     => 'required|in:email,mobile',
        ]);

        $email = $request->type === 'email'  ? $request->email   : null;
        $contact = $request->type === 'mobile' ? $request->contact : null;

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
     * Handle SPA login using session-based auth (Fortify).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful',
            'data' => \auth()->user(),
        ]);
    }

    /**
     * Log the user out and invalidate the session.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }


}
