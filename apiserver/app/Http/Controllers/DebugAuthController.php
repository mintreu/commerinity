<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BeneficiaryAccount;
use App\Models\User;
use App\Services\BeneficiaryAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DebugAuthController extends Controller
{
    public function testFlow(Request $request): JsonResponse
    {
        try {
            $output = [];

            // Step 1: Create or find user
            $user = User::where('email', 'debugflow@test.com')->first();
            if (! $user) {
                $user = User::factory()->create([
                    'email' => 'debugflow@test.com',
                    'password' => Hash::make('password'),
                ]);
                $output['step1_user_created'] = $user->id;
            } else {
                // Clean up existing tokens
                $user->tokens()->delete();
                $output['step1_user_found'] = $user->id;
            }

            // Step 2: Create token
            $token = $user->createToken('debug-device')->plainTextToken;
            $output['step2_token_created'] = substr($token, 0, 20).'...';
            $output['step2_tokens_count'] = $user->tokens()->count();

            // Step 3: Simulate authenticated request
            $request->headers->set('Authorization', 'Bearer '.$token);
            $authenticatedUser = null;

            try {
                // This simulates what Sanctum middleware does
                $tokenId = explode('|', $token)[0];
                $hashedToken = explode('|', $token)[1];
                $tokenModel = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);

                if ($tokenModel && hash_equals($tokenModel->token, hash('sha256', $hashedToken))) {
                    $authenticatedUser = $tokenModel->tokenable;
                    $output['step3_auth_success'] = true;
                    $output['step3_user_id'] = $authenticatedUser->id;
                    $output['step3_current_token_id'] = $tokenModel->id;
                } else {
                    $output['step3_auth_failed'] = true;
                }
            } catch (\Exception $e) {
                $output['step3_error'] = $e->getMessage();
            }

            // Step 4: Simulate logout (delete token)
            if ($authenticatedUser) {
                $tokensBefore = $authenticatedUser->tokens()->count();
                $output['step4_tokens_before_delete'] = $tokensBefore;

                $authenticatedUser->tokens()->where('id', $tokenModel->id)->delete();

                $tokensAfter = $authenticatedUser->tokens()->count();
                $output['step4_tokens_after_delete'] = $tokensAfter;

                // Verify token is gone
                $tokenStillExists = \Laravel\Sanctum\PersonalAccessToken::find($tokenModel->id);
                $output['step4_token_still_exists'] = $tokenStillExists ? 'YES' : 'NO';
            }

            // Step 5: Try to authenticate again with same token
            try {
                $tokenModel2 = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                $output['step5_can_find_token'] = $tokenModel2 ? 'YES' : 'NO';

                if ($tokenModel2) {
                    $output['step5_ERROR'] = 'Token should not be found after deletion!';
                } else {
                    $output['step5_SUCCESS'] = 'Token correctly deleted';
                }
            } catch (\Exception $e) {
                $output['step5_error'] = $e->getMessage();
            }

            // Step 6: Check session state
            $output['step6_has_session'] = $request->hasSession();
            if ($request->hasSession()) {
                $output['step6_session_id'] = $request->session()->getId();
                $output['step6_session_data'] = $request->session()->all();
            }

            return response()->json([
                'success' => true,
                'output' => $output,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }



    public function index(Request $request)
    {
        $beneficiary = BeneficiaryAccount::find(1);

        dd(BeneficiaryAccountService::make($beneficiary)->sync());
    }


}
