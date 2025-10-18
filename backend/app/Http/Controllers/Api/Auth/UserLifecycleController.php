<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Lifecycle\LevelResource;
use App\Http\Resources\Lifecycle\StageResource;
use App\Http\Resources\Lifecycle\UserSubscriptionResource;
use App\Http\Resources\User\UserResource;
use App\Services\UserServices\MembershipSubscriptionService;
use Illuminate\Http\Request;

class UserLifecycleController extends Controller
{



    public function getUserSubscriptionStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $service = MembershipSubscriptionService::make($user);

        return response()->json([
           'active' => $service->isSubscriptionRequired(),
           'subscription' => $service->getSubscription(),
           'upcoming_plan' => StageResource::make($service->getApplicableStage()),
        ]);

    }







}
