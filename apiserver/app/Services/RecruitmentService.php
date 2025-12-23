<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recruitment\Recruitment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service class for handling recruitment listings and queries.
 *
 * Provides read-only operations for retrieving and filtering
 * published recruitment postings.
 */
final class RecruitmentService
{
    private const int DEFAULT_PER_PAGE = 12;

    /**
     * Get paginated list of open recruitments.
     */
    public function listOpen(
        ?string $role = null,
        ?string $employmentType = null,
        int $perPage = self::DEFAULT_PER_PAGE
    ): LengthAwarePaginator {
        return Recruitment::query()
            ->open()
            ->when($role, fn ($q) => $q->where('role', $role))
            ->when($employmentType, fn ($q) => $q->where('employment_type', $employmentType))
            ->with('media')
            ->latest('open_date')
            ->paginate($perPage);
    }

    /**
     * Get all open recruitments without pagination.
     */
    public function getAllOpen(?string $role = null): Collection
    {
        return Recruitment::query()
            ->open()
            ->when($role, fn ($q) => $q->where('role', $role))
            ->with('media')
            ->latest('open_date')
            ->get();
    }

    /**
     * Find a recruitment by slug.
     */
    public function findBySlug(string $slug): ?Recruitment
    {
        return Recruitment::query()
            ->where('slug', $slug)
            ->with('media')
            ->first();
    }

    /**
     * Find an open recruitment by slug.
     */
    public function findOpenBySlug(string $slug): ?Recruitment
    {
        return Recruitment::query()
            ->open()
            ->where('slug', $slug)
            ->with('media')
            ->first();
    }

    /**
     * Get recruitment by ID.
     */
    public function findById(int $id): ?Recruitment
    {
        return Recruitment::find($id);
    }

    /**
     * Check if recruitment is currently accepting applications.
     */
    public function isAcceptingApplications(Recruitment $recruitment): bool
    {
        return $recruitment->is_open;
    }

    /**
     * Get available roles for filtering.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function getAvailableRoles(): array
    {
        $roles = __('recruitment.roles');

        return collect($roles)
            ->map(fn (string $label, string $value) => [
                'value' => $value,
                'label' => $label,
            ])
            ->values()
            ->all();
    }

    /**
     * Get available employment types for filtering.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function getAvailableTypes(): array
    {
        $types = __('recruitment.types');

        return collect($types)
            ->map(fn (string $label, string $value) => [
                'value' => $value,
                'label' => $label,
            ])
            ->values()
            ->all();
    }

    /**
     * Get counts by role for open recruitments.
     *
     * @return array<string, int>
     */
    public function getOpenCountsByRole(): array
    {
        return Recruitment::query()
            ->open()
            ->selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();
    }
}
