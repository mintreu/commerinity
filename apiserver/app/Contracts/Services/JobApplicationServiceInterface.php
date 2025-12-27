<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\Recruitment;
use App\Models\Transaction;
use App\Models\User;

interface JobApplicationServiceInterface
{
    /**
     * Create new application builder
     */
    public static function make(Recruitment $recruitment): self;

    /**
     * Set applicant user
     */
    public function forUser(User $user): self;

    /**
     * Submit the application
     *
     * @param array{
     *   guardian_name: string,
     *   address: string,
     *   education?: string,
     *   skills?: string
     * } $data
     */
    public function apply(array $data): self;

    /**
     * Check if application was successful
     */
    public function successful(): bool;

    /**
     * Check if application failed
     */
    public function failed(): bool;

    /**
     * Get error message
     */
    public function getError(): ?string;

    /**
     * Get all errors
     *
     * @return array<string>
     */
    public function getErrors(): array;

    /**
     * Get the created application
     */
    public function getApplication(): ?JobApplication;

    /**
     * Get transaction if payment required
     */
    public function getTransaction(): ?Transaction;

    /**
     * Get return URL for payment
     */
    public function getReturnUrl(): ?string;

    /**
     * Check if payment is required
     */
    public function requiresPayment(): bool;

    /**
     * Convert to array for API response
     *
     * @return array{
     *   success: bool,
     *   error: ?string,
     *   errors: array,
     *   application: ?array,
     *   requires_payment: bool,
     *   checkout_url: ?string
     * }
     */
    public function toArray(): array;

    /**
     * Submit application after payment confirmed
     */
    public function submitAfterPayment(JobApplication $application, Transaction $transaction): JobApplication;

    /**
     * Get user's applications
     *
     * @return \Illuminate\Database\Eloquent\Collection<JobApplication>
     */
    public static function getUserApplications(User $user): \Illuminate\Database\Eloquent\Collection;

    /**
     * Find user's specific application
     */
    public static function findUserApplication(User $user, string $uuid): ?JobApplication;

    /**
     * Withdraw application
     */
    public static function withdraw(JobApplication $application, ?string $reason = null): JobApplication;
}
