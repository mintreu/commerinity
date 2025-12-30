<?php

declare(strict_types=1);

namespace App\Services\Affiliate;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class AffiliateTreeService
{
    private readonly int $maxDirectChildren;

    public function __construct()
    {
        $this->maxDirectChildren = config('affiliate.matrix.max_direct_children', 5);
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
     * Uses HasRecursiveRelationships ancestors() for single CTE query.
     * NO N+1 - fetches all ancestors in one query, then checks slots in memory.
     */
    public function findAvailableAncestor(User $startingUser): ?User
    {
        // Single query using recursive CTE - no N+1
        $ancestors = $startingUser->ancestors()
            ->depthFirst()
            ->withCount('children')
            ->get();

        foreach ($ancestors as $ancestor) {
            if ($ancestor->children_count < $this->maxDirectChildren) {
                return $ancestor;
            }
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
     * Get Affiliate tree statistics for a user.
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
     *
     * Uses HasRecursiveRelationships for single CTE query.
     * NO N+1 - gets max depth from descendants in one query.
     */
    private function calculateTreeDepth(User $user): int
    {
        // Single query using recursive CTE - no N+1
        return (int) $user->descendants()
            ->selectRaw('MAX(depth) as max_depth')
            ->value('max_depth') ?? 0;
    }

    /**
     * Build tree data for D3.js visualization (flat array format).
     *
     * Matches old_project MemberTreeList pattern.
     * Uses single recursive CTE query - NO N+1.
     *
     * @param  int  $maxDepth  Maximum depth to traverse (default 5)
     * @param  int  $maxChildren  Max children per node (default 5)
     * @return array<int, array<string, mixed>>
     */
    public function buildTreeData(User $user, int $maxDepth = 5, int $maxChildren = 5): array
    {
        $treeData = [];

        // Load root user with media
        $user->loadMissing(['level', 'media']);

        // Add root node
        $treeData[] = $this->formatUserNode($user, null, 0);

        // Get all descendants in single CTE query (NO N+1)
        $descendants = $user->descendants()
            ->whereDepth('<=', $maxDepth)
            ->with(['level', 'media'])
            ->depthFirst()
            ->get()
            ->groupBy('parent_id');

        // Build tree recursively from pre-fetched data
        $this->buildTreeFromDescendants($treeData, $descendants, $user->id, 1, $maxDepth, $maxChildren);

        // Update hasChildren flags
        $parentIds = collect($treeData)->pluck('parentId')->filter()->unique()->values();
        foreach ($treeData as &$node) {
            $node['hasChildren'] = $parentIds->contains($node['id']);
        }

        return $treeData;
    }

    /**
     * Build tree recursively from pre-fetched descendants (no additional queries).
     */
    private function buildTreeFromDescendants(
        array &$treeData,
        Collection $descendants,
        int $parentId,
        int $depth,
        int $maxDepth,
        int $maxChildren
    ): void {
        if ($depth > $maxDepth) {
            return;
        }

        $children = $descendants->get($parentId, collect())->take($maxChildren);

        foreach ($children as $child) {
            $treeData[] = $this->formatUserNode($child, $parentId, $depth);

            // Recurse for this child's children
            $this->buildTreeFromDescendants($treeData, $descendants, $child->id, $depth + 1, $maxDepth, $maxChildren);
        }
    }

    /**
     * Format user node for D3.js tree visualization.
     *
     * @return array<string, mixed>
     */
    private function formatUserNode(User $user, ?int $parentId, int $depth): array
    {
        return [
            'id' => $user->id,
            'parentId' => $parentId,
            'userId' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'image' => $user->getFirstMediaUrl('avatarImage') ?: null,
            'level' => $user->level?->name ?? 'No Level',
            'joinedOn' => $user->created_at?->format('d/m/Y'),
            'depth' => $depth,
            'hasChildren' => false, // Updated after building
            'referral_code' => $user->referral_code,
            'status' => $user->status?->value ?? 'draft',
        ];
    }

    /**
     * Get tree as JSON for D3.js.
     */
    public function getTreeJson(User $user, int $maxDepth = 5, int $maxChildren = 5): string
    {
        return json_encode($this->buildTreeData($user, $maxDepth, $maxChildren), JSON_THROW_ON_ERROR);
    }

    /**
     * Get tree statistics.
     *
     * @return array<string, mixed>
     */
    public function getTreeStatistics(User $user, int $maxDepth = 5): array
    {
        $treeData = $this->buildTreeData($user, $maxDepth);

        return [
            'total_members' => count($treeData),
            'max_depth' => collect($treeData)->max('depth') ?? 0,
            'root_user' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'name' => $user->name,
            ],
        ];
    }
}
