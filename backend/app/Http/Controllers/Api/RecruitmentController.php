<?php

namespace App\Http\Controllers\Api;

use App\Casts\NaukriApplicationStatusCast;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recruitment\ApplyRecruitmentRequest;
use App\Http\Resources\Naukri\NaukriApplicaitonResource;
use App\Http\Resources\Naukri\NaukriIndexResource;
use App\Http\Resources\Naukri\NaukriResource;
use App\Models\Naukri;
use App\Models\NaukriApplication;
use App\Services\RecruitmentService\RecruitmentApplicationCreationService;
use App\Services\UserServices\ApplicationFeesService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class RecruitmentController extends Controller
{


    public function index(Request $request)
    {
        $allRecruitments = Naukri::withinDate()
            ->with([
                'media' => fn($query) => $query->where('collection_name','displayImage')
            ])
            ->where('status',PublishableStatusCast::PUBLISHED)
            ->get();
        return NaukriIndexResource::collection($allRecruitments);
    }


    public function show(Naukri $naukri)
    {
        $naukri->load(['media']);
        return NaukriResource::make($naukri);
    }





    public function apply(Naukri $naukri, ApplyRecruitmentRequest $request)
    {

        // Job status validation
        if ($naukri->status !== PublishableStatusCast::PUBLISHED) {
            return response()->json([
                'data' => [
                    'status'  => false,
                    'message' => 'The recruitment notice was not found.',
                ]
            ], 404);
        }

        // Expired check
        if ($naukri->close_date < now()) {
            return response()->json([
                'data' => [
                    'status'  => false,
                    'message' => 'This recruitment notice has already expired.',
                ]
            ], 400);
        }

        // Not yet open check
        if ($naukri->open_date > now()) {
            return response()->json([
                'data' => [
                    'status'  => false,
                    'message' => 'The application period has not started yet.',
                ]
            ], 400);
        }

        $user = $request->user();

        $selectedAddress = $user->addresses()->firstWhere('uuid',$request->address_uuid);
        $application = $user->applications()->firstWhere('naukri_id',$naukri->id);

        if ($application)
        {
            return response()->json([
                'data' => [
                    'status'  => false,
                    'message' => 'You have already applied for this job.',
                ]
            ], 400);
        }


        $recruitmentApplicationService = RecruitmentApplicationCreationService::make(naukri: $naukri);
        $recruitmentApplicationService->applicant(applicant: $user)->createApplication(data:[
            'naukri_id' => $naukri->id,
            "guardian_name" => $request->guardian_name,
            "address_id" => $selectedAddress->id,
            "company_name" => $request->company_name,
            "educations" => $request->educations,
            "experiences" => $request->experiences,
            "skills" => $request->skills,
            "reference_name" => $request->reference_name,
            "reference_contact" => $request->reference_contact,
            "relocation" => $request->relocation,
            'submitted_at' => now(),
            'status'    => $naukri->is_payable ? NaukriApplicationStatusCast::AWAITING_PAYMENT : NaukriApplicationStatusCast::SUBMITTED
        ]);




        return response()->json($recruitmentApplicationService->toResponse());

//        return response()->json([
//            'data' => [
//                'status' => true,
//                'message' => 'Application submitted successfully.',
//                'redirect' => !is_null($checkoutUrl),
//                'redirect_url' => $checkoutUrl
//            ],
//        ]);

    }





}
