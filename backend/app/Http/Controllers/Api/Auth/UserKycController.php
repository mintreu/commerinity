<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction\KycResource;
use Illuminate\Http\Request;

class UserKycController extends Controller
{


    public function getUserKyc(Request $request)
    {
        $user = $request->user();
        $user->load([
            'kyc' => fn($query) => $query->load('media')
        ]);

        return KycResource::make($user->kyc);


    }


}
