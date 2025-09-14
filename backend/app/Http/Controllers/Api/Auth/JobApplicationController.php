<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Naukri\NaukriApplicaitonResource;
use App\Models\NaukriApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobApplicationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $user->load(['applications','applications.naukri','applications.address']);

        return NaukriApplicaitonResource::collection($user->applications);
    }


    public function show(NaukriApplication $application)
    {
        $application->load(['transaction','address']);


        return NaukriApplicaitonResource::make($application);
    }
}
