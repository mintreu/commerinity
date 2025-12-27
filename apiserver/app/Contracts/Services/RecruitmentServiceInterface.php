<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Recruitment\Recruitment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RecruitmentServiceInterface
{
    /**
     * Get paginated list of open recruitments.
     */
    public function listOpen(
        ?string $role = null,
        ?string $employmentType = null,
        int $perPage = 12
    ): LengthAwarePaginator;

    /**
     * Get all open recruitments without pagination.
     */
    public function getAllOpen(?string $role = null): Collection;

    /**
     * Find recruitment by slug
     */
    public function findBySlug(string $slug): ?Recruitment;

    /**
     * Find open recruitment by slug
     */
    public function findOpenBySlug(string $slug): ?Recruitment;

    /**
     * Find recruitment by ID
     */
    public function findById(int $id): ?Recruitment;

    /**
     * Check if recruitment is accepting applications
     */
    public function isAcceptingApplications(Recruitment $recruitment): bool;

    /**
     * Get available roles for filter
     *
     * @return array<string, string>
     */
    public function getAvailableRoles(): array;

    /**
     * Get available types for filter
     *
     * @return array<string, string>
     */
    public function getAvailableTypes(): array;

    /**
     * Get open recruitment counts by role
     *
     * @return array<string, int>
     */
    public function getOpenCountsByRole(): array;
}
