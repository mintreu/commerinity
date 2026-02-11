<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

/**
 * OnboardingVerifierService
 *
 * Service for verifying and tracking user onboarding step completeness.
 * Used to check which onboarding steps are complete and calculate overall progress.
 */
final class OnboardingVerifierService
{
    /**
     * Onboarding steps configuration.
     * Each step has a name, description, whether it's required, and a verifier method.
     */
    private const STEPS = [
        'profile' => [
            'name' => 'Complete Profile',
            'description' => 'Fill in your name, date of birth, and gender',
            'required' => true,
            'weight' => 1,
        ],
        'mobile' => [
            'name' => 'Verify Mobile',
            'description' => 'Verify your mobile number via OTP',
            'required' => true,
            'weight' => 1,
        ],
        'email' => [
            'name' => 'Verify Email',
            'description' => 'Verify your email address',
            'required' => false,
            'weight' => 1,
        ],
        'address' => [
            'name' => 'Add Address',
            'description' => 'Add at least one delivery address',
            'required' => false,
            'weight' => 1,
        ],
        'kyc' => [
            'name' => 'KYC Verification',
            'description' => 'Submit KYC documents and get approval',
            'required' => false,
            'weight' => 1,
        ],
    ];

    private User $user;

    /** @var array<string, bool> */
    private array $stepStatus = [];

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->verifyAllSteps();
    }

    /**
     * Create a new instance for a user.
     */
    public static function for(User $user): self
    {
        return new self($user);
    }

    /**
     * Verify all onboarding steps.
     */
    private function verifyAllSteps(): void
    {
        $this->stepStatus = [
            'profile' => $this->isProfileComplete(),
            'mobile' => $this->isMobileVerified(),
            'email' => $this->isEmailVerified(),
            'address' => $this->hasAddress(),
            'kyc' => $this->hasApprovedKyc(),
        ];
    }

    /**
     * Check if user has an approved KYC record.
     */
    private function hasApprovedKyc(): bool
    {
        return $this->user->kyc?->isApproved() ?? false;
    }

    /**
     * Check if user has at least one saved address.
     */
    private function hasAddress(): bool
    {
        return $this->user->addresses()->exists();
    }

    /**
     * Check if profile is complete.
     */
    private function isProfileComplete(): bool
    {
        return ! empty($this->user->name)
            && $this->user->dob !== null
            && $this->user->gender !== null;
    }

    /**
     * Check if mobile is verified.
     */
    private function isMobileVerified(): bool
    {
        return $this->user->hasVerifiedMobile();
    }

    /**
     * Check if email is verified.
     */
    private function isEmailVerified(): bool
    {
        return $this->user->hasVerifiedEmail();
    }

    /**
     * Get status of a specific step.
     */
    public function isStepComplete(string $step): bool
    {
        return $this->stepStatus[$step] ?? false;
    }

    /**
     * Get all step statuses.
     *
     * @return array<string, bool>
     */
    public function getStepStatuses(): array
    {
        return $this->stepStatus;
    }

    /**
     * Get detailed step information with status.
     *
     * @return array<string, array{name: string, description: string, required: bool, complete: bool}>
     */
    public function getDetailedSteps(): array
    {
        $result = [];

        foreach (self::STEPS as $key => $config) {
            $result[$key] = [
                'name' => $config['name'],
                'description' => $config['description'],
                'required' => $config['required'],
                'complete' => $this->stepStatus[$key] ?? false,
            ];
        }

        return $result;
    }

    /**
     * Get only required steps with their status.
     *
     * @return array<string, bool>
     */
    public function getRequiredStepStatuses(): array
    {
        $result = [];

        foreach (self::STEPS as $key => $config) {
            if ($config['required']) {
                $result[$key] = $this->stepStatus[$key] ?? false;
            }
        }

        return $result;
    }

    /**
     * Get completed steps count.
     */
    public function getCompletedStepsCount(): int
    {
        return count(array_filter($this->stepStatus));
    }

    /**
     * Get total steps count.
     */
    public function getTotalStepsCount(): int
    {
        return count(self::STEPS);
    }

    /**
     * Get completed required steps count.
     */
    public function getCompletedRequiredStepsCount(): int
    {
        $count = 0;

        foreach (self::STEPS as $key => $config) {
            if ($config['required'] && ($this->stepStatus[$key] ?? false)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get total required steps count.
     */
    public function getTotalRequiredStepsCount(): int
    {
        return count(array_filter(self::STEPS, fn ($config) => $config['required']));
    }

    /**
     * Calculate overall progress percentage (0-100).
     */
    public function getProgress(): int
    {
        $totalWeight = 0;
        $completedWeight = 0;

        foreach (self::STEPS as $key => $config) {
            $totalWeight += $config['weight'];
            if ($this->stepStatus[$key] ?? false) {
                $completedWeight += $config['weight'];
            }
        }

        if ($totalWeight === 0) {
            return 0;
        }

        return (int) round(($completedWeight / $totalWeight) * 100);
    }

    /**
     * Calculate required steps progress percentage (0-100).
     */
    public function getRequiredProgress(): int
    {
        $totalRequired = $this->getTotalRequiredStepsCount();

        if ($totalRequired === 0) {
            return 100;
        }

        return (int) round(($this->getCompletedRequiredStepsCount() / $totalRequired) * 100);
    }

    /**
     * Check if all required steps are complete.
     */
    public function canCompleteOnboarding(): bool
    {
        foreach (self::STEPS as $key => $config) {
            if ($config['required'] && ! ($this->stepStatus[$key] ?? false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get list of missing required steps.
     *
     * @return array<string>
     */
    public function getMissingRequiredSteps(): array
    {
        $missing = [];

        foreach (self::STEPS as $key => $config) {
            if ($config['required'] && ! ($this->stepStatus[$key] ?? false)) {
                $missing[] = $key;
            }
        }

        return $missing;
    }

    /**
     * Get list of missing optional steps.
     *
     * @return array<string>
     */
    public function getMissingOptionalSteps(): array
    {
        $missing = [];

        foreach (self::STEPS as $key => $config) {
            if (! $config['required'] && ! ($this->stepStatus[$key] ?? false)) {
                $missing[] = $key;
            }
        }

        return $missing;
    }

    /**
     * Get next recommended step to complete.
     */
    public function getNextRecommendedStep(): ?string
    {
        // First, check required steps
        foreach (self::STEPS as $key => $config) {
            if ($config['required'] && ! ($this->stepStatus[$key] ?? false)) {
                return $key;
            }
        }

        // Then, check optional steps
        foreach (self::STEPS as $key => $config) {
            if (! $config['required'] && ! ($this->stepStatus[$key] ?? false)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Check if onboarding is fully complete (all steps including optional).
     */
    public function isFullyComplete(): bool
    {
        return $this->getCompletedStepsCount() === $this->getTotalStepsCount();
    }

    /**
     * Get summary of onboarding status.
     *
     * @return array{
     *     progress: int,
     *     required_progress: int,
     *     can_complete: bool,
     *     is_complete: bool,
     *     completed_steps: int,
     *     total_steps: int,
     *     next_step: ?string,
     *     missing_required: array<string>,
     *     steps: array<string, bool>
     * }
     */
    public function getSummary(): array
    {
        return [
            'progress' => $this->getProgress(),
            'required_progress' => $this->getRequiredProgress(),
            'can_complete' => $this->canCompleteOnboarding(),
            'is_complete' => $this->user->onboarded,
            'completed_steps' => $this->getCompletedStepsCount(),
            'total_steps' => $this->getTotalStepsCount(),
            'next_step' => $this->getNextRecommendedStep(),
            'missing_required' => $this->getMissingRequiredSteps(),
            'steps' => $this->stepStatus,
        ];
    }

    /**
     * Get full detailed summary for API response.
     *
     * @return array{
     *     onboarded: bool,
     *     progress: int,
     *     required_progress: int,
     *     can_complete: bool,
     *     completed_steps: int,
     *     total_steps: int,
     *     completed_required: int,
     *     total_required: int,
     *     next_step: ?string,
     *     missing_required: array<string>,
     *     missing_optional: array<string>,
     *     steps: array<string, array{name: string, description: string, required: bool, complete: bool}>
     * }
     */
    public function getFullSummary(): array
    {
        return [
            'onboarded' => $this->user->onboarded,
            'progress' => $this->getProgress(),
            'required_progress' => $this->getRequiredProgress(),
            'can_complete' => $this->canCompleteOnboarding(),
            'completed_steps' => $this->getCompletedStepsCount(),
            'total_steps' => $this->getTotalStepsCount(),
            'completed_required' => $this->getCompletedRequiredStepsCount(),
            'total_required' => $this->getTotalRequiredStepsCount(),
            'next_step' => $this->getNextRecommendedStep(),
            'missing_required' => $this->getMissingRequiredSteps(),
            'missing_optional' => $this->getMissingOptionalSteps(),
            'steps' => $this->getDetailedSteps(),
        ];
    }
}
