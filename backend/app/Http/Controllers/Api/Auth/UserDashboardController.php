<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mintreu\LaravelMoney\LaravelMoney;

class UserDashboardController extends Controller
{


    public function getAccountDashboard(Request $request)
    {
        $user = $request->user();



        return response()->json([
           'data' => [
               'total_earning' => LaravelMoney::format(0),
               'referral_count' => 0,
               'total_order' => 0,
               'current_rank' => '',

           ],
        ]);
    }




}
