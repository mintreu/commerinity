<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserServices\NetworkServices\NetworkService;
use Illuminate\Http\Request;
use Mintreu\LaravelMoney\LaravelMoney;

class UserStatsController extends Controller
{


    public function index(Request $request)
    {
        return response()->json([
           'data' => [
               'success' => true,
               'message' => 'Success!'
           ],
        ]);
    }


    public function getMinimal(Request $request)
    {

        $user = $request->user();
        $orderCount = $user->completeOrders()->count();
        $childrenCount = $user->children()->count();


        return [
            'status' => true,
            'total' => [
                'orders' => $orderCount,
                'children' => $childrenCount,
                'commission' => [
                    'referral' => LaravelMoney::format(0.00),
                    'rewards' => 0,
                ],
            ],
        ];

    }


    public function getUserTree(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        // add filter for check any ?referral_code given
        if (isset($request->referral_code))
        {
            $user = User::firstWhere('referral_code',$request->referral_code);
        }

        if ($user)
        {
            return response()->json([
                'status' => true,
                'data' => NetworkService::make($user)->getTree()->getJson()
            ]);
        }else{
            return response()->json([
                'status' => true,
                'data' => null
            ]);
        }

    }




    public function getMemberStat(User $user,Request $request)
    {

    }







}
