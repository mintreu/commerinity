<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Services\UserServices\MembershipSubscriptionService;
use Illuminate\Http\Request;

class UserSubscriptionController extends Controller
{


    public function subscribeStagePlan(Request $request)
    {

        $user = $request->user();

        $service = MembershipSubscriptionService::make($user);

        if (!$service->isSubscriptionRequired())
        {
            return response()->json([
                'status' => false,
                'message' => 'You already have an active plan.',
                'remaining_days' => now()->diffInDays($user->membership->expire_at, false),
                'expires_at' => $user->membership->expire_at->toDateTimeString(),
            ]);

        }

        // New Subscription
        $service->ensureSubscription();
        $newSubscription = $service->getSubscription();
        dd($newSubscription);


    }










    public function getCurrentSubscription(Request $request)
    {
        $user = $request->user();
        $user->load([
            'membership',
            'membership.stage',
            'membership.level'
        ]);
        return UserResource::make($user);
    }


    public function subscribeSubscription(Request $request)
    {

        $user = $request->user();


        // User want subscription
        $membershipSubscriptionService = MembershipSubscriptionService::make($user);
        $membershipSubscriptionService->ensureSubscription();
        $userSubscription = $membershipSubscriptionService->getSubscription();
        $checkoutUrl = $membershipSubscriptionService->getCheckoutUrl();

        return response()->json([
            'data' => [
                'status' => true,
                'message' => 'User subscription completed successfully.',
                'redirect' => true,
                'redirect_url' => $checkoutUrl
            ],
        ]);


    }



}
