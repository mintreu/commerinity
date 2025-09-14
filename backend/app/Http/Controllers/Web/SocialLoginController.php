<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SocialLoginController extends Controller
{


    public function attempt($provider,Request $request)
    {
        dd($provider);
    }



}
