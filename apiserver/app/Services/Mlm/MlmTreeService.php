<?php

declare(strict_types=1);

namespace App\Services\Mlm;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class MlmTreeService
{
    private readonly int $maxDirectChildren;

    public function __construct()
    {
        $this->maxDirectChildren = config('mlm.matrix.max_direct_children', 5);
    }

    /**
     * Reassign children of a deleted user to ancestors with available slots.
     *
     * When a user is deleted, their direct children need new parents.
     * The algorithm finds the nearest ancestor with available slots
     * and assigns orphaned children there.
     */
    public function reassignChildrenOnDeletion(User $deletedUser): array
    {
        $children = $deletedUser->children()->get();

        if ($children->isEmpty()) {
            return [
                'reassigned' => 0,
                'details' => [],
            ];
        }

        $reassignmentDetails = [];

        DB::transaction(function () use ($deletedUser, $children, &$reassignmentDetails) {
            foreach ($children as $child) {
                $newParent = $this->findAvailableAncestor($deletedUser);

                if ($newParent) {
                    $child->parent_id = $newParent->id;
                    $child->save();

                    $reassignmentDetails[] = [
                        'child_id' => $child->id,
                        'child_uuid' => $child->uuid,
                        'new_parent_id' => $newParent->id,
                        'new_parent_uuid' => $newParent->uuid,
                    ];
                } else {
                    // No available ancestor found, child becomes root
                    $child->parent_id = null;
                    $child->save();

                    $reassignmentDetails[] = [
                        'child_id' => $child->id,
                        'child_uuid' => $child->uuid,
                        'new_parent_id' => null,
                        'new_parent_uuid' => null,
                        'note' => 'Became root user (no ancestor with available slots)',
                    ];
                }
            }
        });

        return [
            'reassigned' => count($reassignmentDetails),
            'details' => $reassignmentDetails,
        ];
    }

    /**
     * Find the nearest ancestor with available slots for new children.
     *
     * Traverses up the tree from the deleted user's parent,
     * checking each ancestor for available child slots.
     */
    public function findAvailableAncestor(User $startingUser): ?User
    {
        $ancestor = $startingUser->parent;

        while ($ancestor !== null) {
            if ($this->hasAvailableSlots($ancestor)) {
                return $ancestor;
            }

            $ancestor = $ancestor->parent;
        }

        return null;
    }

    /**
     * Check if a user has available slots for direct children.
     */
    public function hasAvailableSlots(User $user): bool
    {
        $currentChildCount = $user->children()->count();

        return $currentChildCount < $this->maxDirectChildren;
    }

    /**
     * Get the number of available slots for a user.
     */
    public function getAvailableSlots(User $user): int
    {
        $currentChildCount = $user->children()->count();

        return max(0, $this->maxDirectChildren - $currentChildCount);
    }

    /**
     * Get the complete downline (all descendants) of a user.
     */
    public function getDownline(User $user): Collection
    {
        return $user->descendants()->get();
    }

    /**
     * Get the complete upline (all ancestors) of a user.
     */
    public function getUpline(User $user): Collection
    {
        return $user->ancestors()->get();
    }

    /**
     * Get direct children count for a user.
     */
    public function getDirectChildrenCount(User $user): int
    {
        return $user->children()->count();
    }

    /**
     * Check if user can accept new children.
     */
    public function canAcceptChildren(User $user): bool
    {
        return $this->hasAvailableSlots($user);
    }

    /**
     * Get MLM tree statistics for a user.
     */
    public function getTreeStats(User $user): array
    {
        return [
            'direct_children' => $this->getDirectChildrenCount($user),
            'max_direct_children' => $this->maxDirectChildren,
            'available_slots' => $this->getAvailableSlots($user),
            'total_downline' => $user->descendants()->count(),
            'tree_depth' => $this->calculateTreeDepth($user),
        ];
    }

    /**
     * Calculate the depth of user's downline tree.
     */
    private function calculateTreeDepth(User $user): int
    {
        $maxDepth = 0;

        $children = $user->children;

        if ($children->isEmpty()) {
            return 0;
        }

        foreach ($children as $child) {
            $childDepth = 1 + $this->calculateTreeDepth($child);
            $maxDepth = max($maxDepth, $childDepth);
        }

        return $maxDepth;
    }
}
