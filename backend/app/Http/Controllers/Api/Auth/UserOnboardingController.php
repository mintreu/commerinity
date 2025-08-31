<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lifecycle\UserOnboardingRequest;
use App\Services\UserServices\MembershipSubscriptionService;
use Illuminate\Http\Request;

class UserOnboardingController extends Controller
{




    public function processOnboarding(UserOnboardingRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $user->load('address', 'kyc');

        if (!$request->tnc)
        {
            return response()->json([
                'data' => [
                    'status' => false,
                    'message' => 'User must agree with our terms and conditions to use our services',
                ],
            ]);
        }

        // Verify email and mobile matches logged-in user
        if ($user->email !== $request->email || $user->mobile !== $request->mobile) {
            return response()->json([
                'data' => [
                    'status' => false,
                    'error' => 'Credentials do not match the logged in user.',
                ],
            ], 422);
        }

        // Update user basic info
        $user->update([
            'name'   => $request->name,
            'gender' => $request->gender ?? null,
            'dob'    => $request->dob ?? null,
        ]);

        // Update or create address
        $address = $user->addresses()->hasPostal($request->postal_code)->first();

        if (!$address) {
            $address = $user->addresses()->create([
                'postal_code' => $request->postal_code,
                'state_code'  => $request->state_code,
                'village'     => $request->village,
                'block_id'    => $request->block_id,
                'address_1'   => $request->address_1,
                'landmark'    => $request->landmark ?? null,
                'city'        => $request->city,
                'country'     => config('laravel-geokit.default.country')
            ]);
        } else {
            $address->update([
                'state_code'  => $request->state_code,
                'village'     => $request->village,
                'block_id'    => $request->block_id,
                'address_1'   => $request->address_1,
                'landmark'    => $request->landmark ?? null,
                'city'        => $request->city,
            ]);
        }

        // Update or create KYC data
        $kyc = $user->kyc ?? $user->kyc()->create([]);

        $kyc->update([
            'aadhaar' => $request->aadhaar,
            'pan'     => $request->pan,
        ]);

        // Handle file uploads if present
        if ($request->hasFile('aadhaar_file')) {
            $kyc->clearMediaCollection('aadhaarImage');
            $kyc->addMediaFromRequest('aadhaar_file')->toMediaCollection('aadhaarImage');
        }
        if ($request->hasFile('pan_file')) {
            $kyc->clearMediaCollection('panImage');
            $kyc->addMediaFromRequest('pan_file')->toMediaCollection('panImage');
        }

        // Save subscription type & tnc (assuming these are fields on user model or related)
        if ($request->subscription_type == 'subscribe' && is_null($user->level_id))
        {
            // User want subscription
            $membershipSubscriptionService = MembershipSubscriptionService::make($user);
            $membershipSubscriptionService->ensureSubscription();
            $userSubscription = $membershipSubscriptionService->getSubscription();
            $subscriptionUuid = null;


            return response()->json([
                'data' => [
                    'status' => true,
                    'message' => 'User onboarding completed successfully.',
                    'redirect' => true,
                    'redirect_url' => url('checkout/'.$subscriptionUuid)
                ],
            ]);
        }else{
            // User want to skip subscription and use as regular customer
            return response()->json([
                'data' => [
                    'status' => true,
                    'message' => 'User onboarding completed successfully.',
                    'redirect' => true,
                    'redirect_url' => config('app.client_url').'/dashboard'
                ],
            ]);
        }



    }






}
