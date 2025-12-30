<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Support\Collection;

interface AffiliateTreeServiceInterface
{
    /**
     * Reassign children when a user is deleted
     */
    public function reassignChildrenOnDeletion(User $deletedUser): void;

    /**
     * Find available ancestor with slots for placement
     */
    public function findAvailableAncestor(User $startingUser, int $maxSlots = 5): ?User;

    /**
     * Check if user has available slots for children
     */
    public function hasAvailableSlots(User $user, int $maxSlots = 5): bool;

    /**
     * Get number of available slots for user
     */
    public function getAvailableSlots(User $user, int $maxSlots = 5): int;

    /**
     * Get user's downline (descendants)
     *
     * @return Collection<User>
     */
    public function getDownline(User $user, ?int $depth = null): Collection;

    /**
     * Get user's upline (ancestors)
     *
     * @return Collection<User>
     */
    public function getUpline(User $user, ?int $depth = null): Collection;

    /**
     * Get direct children count
     */
    public function getDirectChildrenCount(User $user): int;

    /**
     * Check if user can accept more children
     */
    public function canAcceptChildren(User $user, int $maxSlots = 5): bool;

    /**
     * Get tree statistics for user
     *
     * @return array{
     *   total_downline: int,
     *   direct_children: int,
     *   active_children: int,
     *   tree_depth: int,
     *   available_slots: int
     * }
     */
    public function getTreeStats(User $user): array;
}
