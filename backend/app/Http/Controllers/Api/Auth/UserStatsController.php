<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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


    public function getMemberStat(User $user,Request $request)
    {

    }







}
