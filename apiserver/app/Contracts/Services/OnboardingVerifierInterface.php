<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\User;

interface OnboardingVerifierInterface
{
    /**
     * Check if specific step is complete
     */
    public function isStepComplete(User $user, string $step): bool;

    /**
     * Get status of all steps
     *
     * @return array<string, bool>
     */
    public function getStepStatuses(User $user): array;

    /**
     * Get detailed step information
     *
     * @return array<string, array{
     *   complete: bool,
     *   required: bool,
     *   label: string,
     *   description: string
     * }>
     */
    public function getDetailedSteps(User $user): array;

    /**
     * Get onboarding progress percentage (0-100)
     */
    public function getProgress(User $user): int;

    /**
     * Check if user can complete onboarding
     */
    public function canCompleteOnboarding(User $user): bool;

    /**
     * Get summary of missing requirements
     *
     * @return array{
     *   can_complete: bool,
     *   missing: array<string>,
     *   progress: int
     * }
     */
    public function getSummary(User $user): array;

    /**
     * Get full onboarding summary with all details
     *
     * @return array{
     *   can_complete: bool,
     *   missing: array<string>,
     *   progress: int,
     *   steps: array,
     *   next_step: ?string
     * }
     */
    public function getFullSummary(User $user): array;
}
