<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Kyc;
use App\Models\User;

interface KycServiceInterface
{
    /**
     * Check if user can submit KYC
     */
    public function canSubmitKyc(User $user): bool;

    /**
     * Submit KYC for user
     *
     * @param array{
     *   document_type: string,
     *   document_number: string,
     *   front_image?: \Illuminate\Http\UploadedFile,
     *   back_image?: \Illuminate\Http\UploadedFile
     * } $data
     */
    public function submitKyc(User $user, array $data): Kyc;

    /**
     * Resubmit KYC after rejection
     *
     * @param array{
     *   document_type?: string,
     *   document_number?: string,
     *   front_image?: \Illuminate\Http\UploadedFile,
     *   back_image?: \Illuminate\Http\UploadedFile
     * } $data
     */
    public function resubmitKyc(User $user, array $data): Kyc;

    /**
     * Get user's KYC status summary
     *
     * @return array{
     *   status: string,
     *   can_submit: bool,
     *   can_resubmit: bool,
     *   kyc: ?Kyc,
     *   rejection_reason: ?string
     * }
     */
    public function getUserKycStatus(User $user): array;
}
