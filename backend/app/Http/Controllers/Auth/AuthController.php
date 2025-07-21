<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
