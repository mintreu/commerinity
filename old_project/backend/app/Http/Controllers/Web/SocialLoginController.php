<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function attempt($provider, Request $request)
    {
        // Validate provider
        $allowedProviders = ['google', 'facebook', 'github']; // Add supported providers

        if (!in_array($provider, $allowedProviders)) {
            abort(404, 'Social provider not supported');
        }

        // Redirect to social provider
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider, Request $request)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            // Handle social user authentication/registration
            // This would typically create or find user and log them in
            // For now, just return the user data
            return response()->json([
                'provider' => $provider,
                'social_user' => $socialUser
            ]);
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Social login failed');
        }
    }
}
