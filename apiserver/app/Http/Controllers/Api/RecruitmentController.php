<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Casts\JobApplicationStatusCast;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyJobRequest;
use App\Http\Resources\JobApplicationResource;
use App\Http\Resources\RecruitmentResource;
use App\Models\Recruitment\Recruitment;
use App\Services\Recruitment\JobApplicationNotificationService;
use App\Services\Recruitment\JobApplicationService;
use App\Services\Recruitment\RecruitmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * API Controller for recruitment/career endpoints.
 *
 * Handles public recruitment listings and authenticated job applications.
 */
class RecruitmentController extends Controller
{
    public function __construct(
        private readonly RecruitmentService $recruitmentService,
        private readonly JobApplicationNotificationService $jobApplicationNotificationService,
    ) {}

    /**
     * List all open recruitments (public).
     *
     * GET /api/careers
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $recruitments = $this->recruitmentService->listOpen(
            role: $request->query('role'),
            employmentType: $request->query('type'),
            perPage: (int) $request->query('per_page', 12)
        );

        return RecruitmentResource::collection($recruitments);
    }

    /**
     * Show a specific recruitment by slug (public).
     *
     * GET /api/careers/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $recruitment = $this->recruitmentService->findBySlug($slug);

        if (! $recruitment) {
            return response()->json([
                'message' => 'Recruitment not found.',
            ], 404);
        }

        return response()->json([
            'data' => new RecruitmentResource($recruitment),
        ]);
    }

    /**
     * Get filter options for recruitments (public).
     *
     * GET /api/careers/filters
     */
    public function filters(): JsonResponse
    {
        return response()->json([
            'data' => [
                'roles' => $this->recruitmentService->getAvailableRoles(),
                'types' => $this->recruitmentService->getAvailableTypes(),
                'counts_by_role' => $this->recruitmentService->getOpenCountsByRole(),
            ],
        ]);
    }

    /**
     * Apply for a recruitment (authenticated).
     *
     * POST /api/careers/{slug}/apply
     */
    public function apply(ApplyJobRequest $request, string $slug): JsonResponse
    {
        $recruitment = $this->recruitmentService->findOpenBySlug($slug);

        if (! $recruitment) {
            return response()->json([
                'message' => 'This recruitment is not accepting applications.',
            ], 404);
        }

        $result = JobApplicationService::make($recruitment)
            ->forUser($request->user())
            ->apply($request->validated());

        if ($result->failed()) {
            return response()->json([
                'message' => $result->getError(),
                'errors' => $result->getErrors(),
            ], 422);
        }

        $application = $result->getApplication();
        $application->load(['recruitment', 'address']);

        $this->jobApplicationNotificationService->notifyApplied(
            $request->user(),
            $application,
            $result->requiresPayment(),
        );

        return response()->json([
            'message' => $result->requiresPayment()
                ? 'Application created. Please complete payment to submit.'
                : 'Application submitted successfully.',
            'data' => [
                'application' => new JobApplicationResource($application),
                'requires_payment' => $result->requiresPayment(),
                'payment_url' => $result->getReturnUrl(),
            ],
        ], 201);
    }

    /**
     * Get user's job applications (authenticated).
     *
     * GET /api/my-applications
     */
    public function myApplications(Request $request): AnonymousResourceCollection
    {
        $applications = JobApplicationService::getUserApplications($request->user());

        return JobApplicationResource::collection($applications);
    }

    /**
     * Get a specific application by UUID (authenticated).
     *
     * GET /api/my-applications/{uuid}
     */
    public function showApplication(Request $request, string $uuid): JsonResponse
    {
        $application = JobApplicationService::findUserApplication($request->user(), $uuid);

        if (! $application) {
            return response()->json([
                'message' => 'Application not found.',
            ], 404);
        }

        return response()->json([
            'data' => new JobApplicationResource($application),
        ]);
    }


    /**
     * Check if user has already applied for a recruitment.
     *
     * GET /api/careers/{slug}/check-application
     */
    public function checkApplication(Request $request, string $slug): JsonResponse
    {
        $recruitment = $this->recruitmentService->findBySlug($slug);

        if (! $recruitment) {
            return response()->json([
                'message' => 'Recruitment not found.',
            ], 404);
        }

        $user = $request->user();
        $hasApplied = $user ? $user->hasAppliedTo($recruitment->id) : false;
        $application = $hasApplied ? $user->getApplicationFor($recruitment->id) : null;

        return response()->json([
            'data' => [
                'has_applied' => $hasApplied,
                'application' => $application ? new JobApplicationResource($application->load('recruitment')) : null,
            ],
        ]);
    }

    /**
     * Initiate payment for an application awaiting payment.
     *
     * POST /api/my-applications/{uuid}/pay
     */
    public function initiatePayment(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'payment_method' => ['required', 'string', 'in:wallet,online'],
        ]);

        $application = JobApplicationService::findUserApplication($request->user(), $uuid);

        if (! $application) {
            return response()->json([
                'message' => 'Application not found.',
            ], 404);
        }

        if ($application->is_paid) {
            return response()->json([
                'message' => 'This application has already been paid.',
            ], 400);
        }




        if ($application->status->value !== JobApplicationStatusCast::AwaitingPayment->value) {
            return response()->json([
                'message' => 'This application is not awaiting payment.',
            ], 400);
        }

        // Create payment transaction
        $result = JobApplicationService::initiatePayment(
            $application,
            $request->user(),
            $request->input('payment_method')
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to initiate payment.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment initiated. Please complete payment.',
            'data' => [
                'checkout_url' => $result['checkout_url'],
                'transaction_uuid' => $result['transaction_uuid'],
            ],
        ]);
    }
}
