<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{


    public function getAccountDashboard(Request $request)
    {
        $user = $request->user();



    }




}
