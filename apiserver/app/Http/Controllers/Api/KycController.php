<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitKycRequest;
use App\Http\Resources\KycResource;
use App\Models\Kyc;
use App\Services\KycService;
use Illuminate\Http\JsonResponse;

final class KycController extends Controller
{
    public function __construct(
        private readonly KycService $kycService
    ) {}

    public function status(): JsonResponse
    {
        $user = auth()->user();
        $kyc = $this->kycService->getUserKycStatus($user);

        if (! $kyc) {
            return response()->json([
                'message' => 'No KYC record found',
                'data' => ['has_kyc' => false, 'kyc' => null],
            ]);
        }

        return response()->json([
            'message' => 'KYC status retrieved successfully',
            'data' => ['has_kyc' => true, 'kyc' => new KycResource($kyc)],
        ]);
    }

    public function submit(SubmitKycRequest $request): JsonResponse
    {
        $user = $request->user();

        $canSubmit = $this->kycService->canSubmitKyc($user);

        if (! $canSubmit['can_submit']) {
            return response()->json([
                'message' => $canSubmit['reason'],
                'data' => ['kyc' => new KycResource($canSubmit['kyc'])],
            ], 422);
        }

        $documents = $request->hasFile('documents') ? $request->file('documents') : null;
        $kyc = $this->kycService->submitKyc($user, $request->validated(), $documents);

        return response()->json([
            'message' => 'KYC submitted successfully for verification',
            'data' => ['kyc' => new KycResource($kyc->load('media'))],
        ], 201);
    }

    public function resubmit(SubmitKycRequest $request, Kyc $kyc): JsonResponse
    {
        $user = $request->user();

        if ($kyc->kycable_id !== $user->id || $kyc->kycable_type !== get_class($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $documents = $request->hasFile('documents') ? $request->file('documents') : null;
            $kyc = $this->kycService->resubmitKyc($kyc, $request->validated(), $documents);

            return response()->json([
                'message' => 'KYC resubmitted successfully',
                'data' => ['kyc' => new KycResource($kyc->load('media'))],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
