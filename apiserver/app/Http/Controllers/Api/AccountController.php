<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Mlm\MlmTreeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class AccountController extends Controller
{
    public function __construct(
        private readonly MlmTreeService $mlmTreeService,
    ) {}

    /**
     * Delete the authenticated user's account.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
            'confirmation' => ['required', 'string', 'in:DELETE'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password',
                'errors' => ['password' => ['The provided password is incorrect.']],
            ], 422);
        }

        try {
            $reassignmentResult = DB::transaction(function () use ($user) {
                $reassignmentResult = $this->mlmTreeService->reassignChildrenOnDeletion($user);
                $user->tokens()->delete();
                if (method_exists($user, 'pushSubscriptions')) {
                    $user->pushSubscriptions()->delete();
                }
                if (config('mlm.deletion.soft_delete_users', true)) {
                    if (method_exists($user, 'trashed')) {
                        $user->delete();
                    } else {
                        $user->status = 'deleted';
                        $user->email = 'deleted_'.$user->id.'_'.$user->email;
                        $user->mobile = 'deleted_'.$user->id.'_'.$user->mobile;
                        $user->save();
                    }
                } else {
                    $user->forceDelete();
                }

                return $reassignmentResult;
            });

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully',
                'data' => ['mlm_reassignment' => ['children_reassigned' => $reassignmentResult['reassigned']]],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get MLM tree statistics for the authenticated user.
     */
    public function mlmStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->mlmTreeService->getTreeStats($user);

        return response()->json(['success' => true, 'data' => $stats]);
    }

    /**
     * Get direct children of the authenticated user.
     */
    public function directChildren(Request $request): JsonResponse
    {
        $user = $request->user();
        $children = $user->children()
            ->select(['id', 'uuid', 'name', 'email', 'type', 'status', 'created_at'])
            ->paginate($request->input('per_page', 20));

        return response()->json(['success' => true, 'data' => $children]);
    }

    /**
     * Get upline (ancestors) of the authenticated user.
     */
    public function upline(Request $request): JsonResponse
    {
        $user = $request->user();
        $upline = $this->mlmTreeService->getUpline($user)
            ->map(fn ($ancestor) => [
                'id' => $ancestor->id,
                'uuid' => $ancestor->uuid,
                'name' => $ancestor->name,
                'type' => $ancestor->type,
                'level' => $ancestor->depth ?? null,
            ]);

        return response()->json(['success' => true, 'data' => $upline]);
    }

    /**
     * Get full tree data for org chart visualization.
     */
    public function tree(Request $request): JsonResponse
    {
        $user = $request->user();

        $referralCode = $request->input('referral_code');
        if ($referralCode) {
            $targetUser = \App\Models\User::where('referral_code', $referralCode)->first();
            if (! $targetUser) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }
            $user = $targetUser;
        }

        $treeData = $this->buildTreeData($user);

        return response()->json(['success' => true, 'data' => $treeData]);
    }

    /**
     * Build tree data recursively for org chart.
     */
    private function buildTreeData(\App\Models\User $user, int $depth = 0): array
    {
        $result = [];

        $result[] = [
            'id' => (string) $user->id,
            'parentId' => $user->parent_id ? (string) $user->parent_id : '',
            'userId' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'referral_code' => $user->referral_code,
            'type' => $user->type->value,
            'level' => $user->type->getLabel(),
            'depth' => $depth,
            'image' => $user->getFirstMediaUrl('avatar') ?: null,
            'hasChildren' => $user->children()->count() > 0,
            'joinedOn' => $user->created_at?->format('M d, Y'),
        ];

        $children = $user->children()
            ->select(['id', 'parent_id', 'uuid', 'name', 'email', 'referral_code', 'type', 'created_at'])
            ->get();

        foreach ($children as $child) {
            $childData = $this->buildTreeData($child, $depth + 1);
            $result = array_merge($result, $childData);
        }

        return $result;
    }
}
