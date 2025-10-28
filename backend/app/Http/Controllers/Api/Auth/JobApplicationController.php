<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recruitment\ApplyRecruitmentRequest;
use App\Http\Resources\Recruitment\JobApplicationIndexResource;
use App\Http\Resources\Recruitment\JobApplicationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Mintreu\LaravelNaukriManager\Models\NaukriApplication;
use Mintreu\LaravelRecruitment\LaravelRecruitment;
use Mintreu\LaravelRecruitment\Models\JobApplication;
use Mintreu\LaravelRecruitment\Models\Recruitment;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class JobApplicationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $user->load(['applications','applications.recruitment','applications.address']);

        return JobApplicationIndexResource::collection($user->applications);
    }


    public function show(JobApplication $application)
    {
        $application->load(['transaction','address']);

        return JobApplicationResource::make($application);
    }





    public function apply(Recruitment $recruitment, ApplyRecruitmentRequest $request)
    {
        // Helper for uniform error responses
        $jsonError = fn(string $message, int $statusCode) => response()->json([
            'data' => [
                'status'  => false,
                'message' => $message,
            ]
        ], $statusCode);

        // Validate recruitment publication status
        if ($recruitment->status !== PublishableStatusCast::PUBLISHED) {
            return $jsonError('The recruitment notice was not found.', 404);
        }

        // Validate recruitment open/close dates
        $now = now();

        if ($recruitment->close_date < $now) {
            return $jsonError('This recruitment notice has already expired.', 400);
        }

        if ($recruitment->open_date > $now) {
            return $jsonError('The application period has not started yet.', 400);
        }

        // Authenticate user
        $user = $request->user();
        if (!$user) {
            return $jsonError('Authentication failed!', 401);
        }

        // Proceed with application
        $result = LaravelRecruitment::make($recruitment)->user($user)->apply($request->all())->toArray();

        $application  = $result['application'];


        // Return success response
        return response()->json([
            'data' => [
                'status'  => is_null($result['error']),
                'message' => $result['error'] ?? 'Application submitted successfully.',
//                'application' => $application,
                'redirect' => !is_null($result['return_url']),
                'redirect_url' => $result['return_url']
            ]
        ], 200);
    }













}
