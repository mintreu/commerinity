<?php

namespace App\Http\Controllers\Api;

use App\Casts\NaukriApplicationStatusCast;
use App\Http\Controllers\Controller;
use App\Http\Resources\Naukri\NaukriApplicaitonResource;
use App\Http\Resources\Naukri\NaukriIndexResource;
use App\Http\Resources\Naukri\NaukriResource;
use App\Models\Naukri;
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


    public function getUserSubmittedApplications(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $user->load(['applications','applications.naukri','applications.address']);

        return NaukriApplicaitonResource::collection($user->applications);
    }


    public function apply(Naukri $naukri, Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email'],
            'mobile'            => ['required', 'string', 'max:15'],
            'gender'            => ['required', 'in:male,female,other'],
            'dob'               => ['required', 'date'],
            'guardian_name'     => ['nullable', 'string', 'max:255'],
            'address_uuid'      => ['required', 'exists:addresses,uuid'],
            'company_name'      => ['nullable', 'string', 'max:255'],
            'educations'        => ['required', 'array', 'min:1'],
            'educations.*.degree'      => ['required', 'string'],
            'educations.*.institution' => ['required', 'string'],
            'educations.*.year'        => ['required', 'integer'],
            'experiences'       => ['nullable', 'array'],
            'experiences.*.company_name' => ['required_with:experiences', 'string'],
            'experiences.*.start'       => ['required_with:experiences', 'date'],
            'experiences.*.end'         => ['required_with:experiences', 'date', 'after_or_equal:experiences.*.start'],
            'experiences.*.experience'  => ['required_with:experiences', 'string'],
            'skills'            => ['required', 'array', 'min:1'],
            'skills.*.skill'    => ['required', 'string'],
            'skills.*.description' => ['required', 'string'],
            'reference_name'    => ['nullable', 'string'],
            'reference_contact' => ['nullable', 'string'],
            'relocation'        => ['required', 'boolean'],
            'agree_terms'       => ['accepted'],
        ]);

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

        if ($user->email != $request->email || $user->mobile != $request->mobile)
        {
            return response()->json([
                'data' => [
                    'status'  => false,
                    'message' => 'Credential not matched, please use correct credentials',
                ]
            ], 400);
        }



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
        }else{
            // Fresh User Job Application
            $application = $user->applications()->create([
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
        }


        $checkoutUrl =  null;

        // Now Naukri Fees Related
        if ($naukri->is_payable)
        {
            $applicationFeesService = ApplicationFeesService::make($naukri);
            $checkoutUrl = $applicationFeesService->application($application)->getCheckoutUrl();
        }
        return response()->json([
            'data' => [
                'status' => true,
                'message' => 'Application submitted successfully.',
                'redirect' => !is_null($checkoutUrl),
                'redirect_url' => $checkoutUrl
            ],
        ]);

    }





}
