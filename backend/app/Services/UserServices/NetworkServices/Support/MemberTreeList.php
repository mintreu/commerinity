<?php

namespace App\Services\UserServices\NetworkServices\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Member Tree List Generator
 * Simple and production-ready tree builder for D3.js
 * Max depth: 5 levels | Max children: 5 per node (5x5 matrix)
 */
class MemberTreeList
{
    protected Model $user;
    protected array $treeData = [];
    protected int $maxDepth = 5;
    protected int $maxChildren = 5;
    protected int $currentDepth = 0;

    public function __construct(Model|User $user)
    {
        $this->user = $user;
    }

    /**
     * Static factory
     */
    public static function make(Model|User $user): static
    {
        return new static($user);
    }

    /**
     * Quick static method to get tree
     */
    public static function tree(Model|User $user): array
    {
        return (new static($user))->getTree();
    }

    /**
     * Quick static method to get JSON
     */
    public static function json(Model|User $user): string
    {
        return (new static($user))->getJson();
    }

    /**
     * Set max depth
     */
    public function withDepth(int $depth): static
    {
        $this->maxDepth = $depth;
        return $this;
    }

    /**
     * Set max children per node
     */
    public function withLimit(int $limit): static
    {
        $this->maxChildren = $limit;
        return $this;
    }

    /**
     * Get tree as array
     */
    public function getTree(): array
    {
        if (empty($this->treeData)) {
            $this->buildTree();
        }
        return $this->treeData;
    }

    /**
     * Get tree as JSON
     */
    public function getJson(): string
    {
        if (empty($this->treeData)) {
            $this->buildTree();
        }
        return json_encode($this->treeData, JSON_THROW_ON_ERROR);
    }

    /**
     * Build the tree structure
     */
    protected function buildTree(): void
    {
        // Reset
        $this->treeData = [];
        $this->currentDepth = 0;

        // Load relationships
        $this->user->loadMissing(['level', 'media']);

        // Add root node
        $this->treeData[] = [
            'id' => $this->user->id,
            'parentId' => null,
            'userId' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email ?? null,
            'image' => $this->user->getFirstMediaUrl('avatarImage') ?: null,
            'level' => $this->user->level?->name ?? 'No Level',
            'joinedOn' => $this->user->created_at->format('d/m/Y'),
            'depth' => 0,
            'hasChildren' => false, // Will update after loading children
            'referral_code' => $this->user->referral_code
        ];

        // Load children recursively
        $this->loadChildren($this->user, $this->user->id, 1);
    }

    /**
     * Recursively load children
     */
    protected function loadChildren(Model $parent, int $parentId, int $depth): void
    {
        // Stop if max depth reached
        if ($depth > $this->maxDepth) {
            return;
        }

        // Get direct children from database (simple query)
        $children = $parent->children()
            ->with(['level', 'media'])
            ->orderBy('created_at')
            ->limit($this->maxChildren)
            ->get();

        // If no children, return
        if ($children->isEmpty()) {
            return;
        }

        // Update parent's hasChildren flag
        $parentIndex = array_search($parentId, array_column($this->treeData, 'id'));
        if ($parentIndex !== false) {
            $this->treeData[$parentIndex]['hasChildren'] = true;
        }

        // Add children to tree
        foreach ($children as $child) {
            $this->treeData[] = [
                'id' => $child->id,
                'parentId' => $parentId,
                'userId' => $child->id,
                'name' => $child->name,
                'email' => $child->email ?? null,
                'image' => $child->getFirstMediaUrl('avatarImage') ?: null,
                'level' => $child->level?->name ?? 'No Level',
                'joinedOn' => $child->created_at->format('d/m/Y'),
                'depth' => $depth,
                'hasChildren' => false, // Will update if it has children
                'referral_code' => $child->referral_code
            ];

            // Recursively load this child's children
            $this->loadChildren($child, $child->id, $depth + 1);
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        if (empty($this->treeData)) {
            $this->buildTree();
        }

        return [
            'total_members' => count($this->treeData),
            'max_depth' => collect($this->treeData)->max('depth') ?? 0,
            'root_user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
        ];
    }
}
