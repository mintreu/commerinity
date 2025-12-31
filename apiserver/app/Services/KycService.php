<?php

declare(strict_types=1);

namespace App\Services;

use App\Casts\KycStatusCast;
use App\Models\Kyc;
use App\Models\User;
use Illuminate\Http\UploadedFile;

final class KycService
{
    /**
     * Check if user can submit KYC
     */
    public function canSubmitKyc(User $user): array
    {
        $existingKyc = $user->kyc;

        if (! $existingKyc) {
            return ['can_submit' => true, 'reason' => null, 'kyc' => null];
        }

        if ($existingKyc->isApproved()) {
            return [
                'can_submit' => false,
                'reason' => 'You already have an approved KYC',
                'kyc' => $existingKyc,
            ];
        }

        if ($existingKyc->isPending()) {
            return [
                'can_submit' => false,
                'reason' => 'You already have a pending KYC submission',
                'kyc' => $existingKyc,
            ];
        }

        return ['can_submit' => true, 'reason' => null, 'kyc' => $existingKyc];
    }

    /**
     * Submit new KYC for verification
     */
    public function submitKyc(User $user, array $data, ?array $documents = null): Kyc
    {
        $kyc = $user->kyc()->create([
            'kyc_type' => $data['kyc_type'],
            'pan_number' => $data['pan_number'],
            'aadhaar_number' => $data['aadhaar_number'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'company_type' => $data['company_type'] ?? null,
            'gst_number' => $data['gst_number'] ?? null,
            'status' => KycStatusCast::PENDING,
            'submitted_at' => now(),
        ]);

        if (request()->hasFile('pan_image')) {
            $kyc->addMediaFromRequest('pan_image')->toMediaCollection('pan_image');
        }
        if (request()->hasFile('aadhaar_image')) {
            $kyc->addMediaFromRequest('aadhaar_image')->toMediaCollection('aadhaar_image');
        }
        if (request()->hasFile('gst_image')) {
            $kyc->addMediaFromRequest('gst_image')->toMediaCollection('gst_image');
        }

        if ($documents) {
            $this->attachDocuments($kyc, $documents);
        }

        return $kyc->fresh();
    }

    /**
     * Resubmit rejected KYC
     */
    public function resubmitKyc(Kyc $kyc, array $data, ?array $documents = null): Kyc
    {
        if (! $kyc->isRejected()) {
            throw new \RuntimeException('Only rejected KYC can be resubmitted');
        }

        $kyc->update([
            'kyc_type' => $data['kyc_type'],
            'pan_number' => $data['pan_number'],
            'aadhaar_number' => $data['aadhaar_number'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'company_type' => $data['company_type'] ?? null,
            'gst_number' => $data['gst_number'] ?? null,
            'status' => KycStatusCast::PENDING,
            'submitted_at' => now(),
            'rejection_reason' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);

        if (request()->hasFile('pan_image')) {
            $kyc->clearMediaCollection('pan_image');
            $kyc->addMediaFromRequest('pan_image')->toMediaCollection('pan_image');
        }
        if (request()->hasFile('aadhaar_image')) {
            $kyc->clearMediaCollection('aadhaar_image');
            $kyc->addMediaFromRequest('aadhaar_image')->toMediaCollection('aadhaar_image');
        }
        if (request()->hasFile('gst_image')) {
            $kyc->clearMediaCollection('gst_image');
            $kyc->addMediaFromRequest('gst_image')->toMediaCollection('gst_image');
        }

        $kyc->clearMediaCollection('documents');

        if ($documents) {
            $this->attachDocuments($kyc, $documents);
        }

        return $kyc->fresh();
    }

    /**
     * Get user's KYC status
     */
    public function getUserKycStatus(User $user): ?Kyc
    {
        return $user->kyc;
    }

    /**
     * Attach documents to KYC
     */
    private function attachDocuments(Kyc $kyc, array $documents): void
    {
        foreach ($documents as $document) {
            if ($document instanceof UploadedFile) {
                $kyc->addMedia($document)->toMediaCollection('documents');
            }
        }
    }
}
