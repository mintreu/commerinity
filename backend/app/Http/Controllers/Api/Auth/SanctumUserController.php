<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserIndexResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Mintreu\LaravelGeokit\Casts\AddressTypeCast;

class SanctumUserController extends Controller
{



    public function getUser(Request $request): UserIndexResource
    {
       // return $request->user();
        return UserIndexResource::make($request->user());
    }

    public function getProfile(Request $request): UserResource
    {
        $user = $request->user();
        $user->load([
            'address' => fn($query) => $query->where('type',AddressTypeCast::HOME),
            'kyc',
            'kyc.media'
        ]);

        return UserResource::make($user);
    }






}
