<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyJobRequest;
use App\Http\Resources\JobApplicationResource;
use App\Http\Resources\RecruitmentResource;
use App\Models\Recruitment\Recruitment;
use App\Services\JobApplicationService;
use App\Services\RecruitmentService;
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
     * Withdraw an application (authenticated).
     *
     * POST /api/my-applications/{uuid}/withdraw
     */
    public function withdrawApplication(Request $request, string $uuid): JsonResponse
    {
        $application = JobApplicationService::findUserApplication($request->user(), $uuid);

        if (! $application) {
            return response()->json([
                'message' => 'Application not found.',
            ], 404);
        }

        if (! $application->can_withdraw) {
            return response()->json([
                'message' => 'This application cannot be withdrawn.',
            ], 422);
        }

        $reason = $request->input('reason');
        JobApplicationService::withdraw($application, $reason);

        return response()->json([
            'message' => 'Application withdrawn successfully.',
            'data' => new JobApplicationResource($application->fresh(['recruitment'])),
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
}
