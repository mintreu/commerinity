<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mlm\MlmGenealogy;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PublicProfileController
 *
 * Handles viewing profiles of other users based on MLM relationship.
 * Visibility rules:
 * - Parent (sponsor) can view limited info about their children (direct referrals)
 * - Parent can drill down to see children's children
 * - Only shows non-sensitive data (no phone/email/address details)
 */
final class PublicProfileController extends Controller
{
    /**
     * View a user's public profile.
     * Only accessible to users in the same MLM tree (upline can see downline).
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $viewer = $request->user();

        // Check if viewer can see this profile
        $relationship = $this->getRelationship($viewer, $user);

        if (! $relationship) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this profile',
            ], 403);
        }

        // Return profile based on relationship type
        return response()->json([
            'success' => true,
            'data' => $this->buildProfileData($user, $viewer, $relationship),
        ]);
    }

    /**
     * Get the team/downline of a user (for drilling down).
     * Only accessible to upline users.
     */
    public function team(Request $request, User $user): JsonResponse
    {
        $viewer = $request->user();

        // Check if viewer can see this user's team
        $relationship = $this->getRelationship($viewer, $user);

        if (! $relationship || $relationship === 'self') {
            // For self, redirect to own team endpoint
            if ($viewer->id === $user->id) {
                return response()->json([
                    'success' => true,
                    'data' => $this->getDirectReferrals($user, $viewer),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this team',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->getDirectReferrals($user, $viewer),
        ]);
    }

    /**
     * Get relationship between viewer and target user.
     *
     * @return string|null 'self', 'upline', 'direct', 'downline', or null if no relationship
     */
    private function getRelationship(User $viewer, User $target): ?string
    {
        // Self
        if ($viewer->id === $target->id) {
            return 'self';
        }

        // Direct sponsor (viewer is the sponsor)
        if ($target->parent_id === $viewer->id) {
            return 'direct';
        }

        // Check if viewer is in target's upline (viewer is upline of target)
        $targetGenealogy = MlmGenealogy::where('user_id', $target->id)->first();
        if ($targetGenealogy && $this->isInPath($viewer->id, $targetGenealogy->path ?? '')) {
            return 'upline';
        }

        // Check if target is viewer's direct child (for reverse view)
        $viewerChildren = $viewer->children()->pluck('id')->toArray();
        if (in_array($target->id, $viewerChildren)) {
            return 'direct';
        }

        // Check deeper downline (viewer can view their entire downline)
        if ($this->isInDownline($viewer, $target)) {
            return 'downline';
        }

        return null;
    }

    /**
     * Check if user ID is in the path string.
     */
    private function isInPath(int $userId, string $path): bool
    {
        $pathIds = explode('/', trim($path, '/'));

        return in_array((string) $userId, $pathIds);
    }

    /**
     * Check if target is in viewer's downline.
     */
    private function isInDownline(User $viewer, User $target): bool
    {
        $targetGenealogy = MlmGenealogy::where('user_id', $target->id)->first();

        if (! $targetGenealogy || ! $targetGenealogy->path) {
            return false;
        }

        return $this->isInPath($viewer->id, $targetGenealogy->path);
    }

    /**
     * Build profile data based on relationship.
     */
    private function buildProfileData(User $user, User $viewer, string $relationship): array
    {
        // Base info visible to all relationships
        $data = [
            'uuid' => $user->uuid,
            'name' => $user->name,
            'avatar_url' => $user->getFirstMediaUrl('avatar'),
            'referral_code' => $user->referral_code,
            'relationship' => $relationship,
            'joined_at' => $user->created_at->format('Y-m-d'),
            'member_since' => $user->created_at->diffForHumans(),
        ];

        // For self, show everything
        if ($relationship === 'self') {
            return array_merge($data, [
                'email' => $user->email,
                'mobile' => $user->mobile,
                'type' => $user->type->value,
                'status' => $user->status->value,
            ]);
        }

        // MLM stats (visible to upline)
        $genealogy = MlmGenealogy::where('user_id', $user->id)->first();
        $data['mlm_stats'] = [
            'level' => $genealogy?->depth ?? 0,
            'direct_referrals' => $genealogy?->direct_count ?? 0,
            'team_size' => $genealogy?->total_descendants ?? 0,
            'active_referrals' => $user->children()
                ->whereHas('subscriptions', fn ($q) => $q->where('status', 'active'))
                ->count(),
        ];

        // Subscription status (general info)
        $activeSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->with('stage')
            ->first();

        $data['subscription'] = [
            'is_active' => (bool) $activeSubscription,
            'stage' => $activeSubscription?->stage?->name ?? 'None',
        ];

        // Location (city/state only, no full address)
        $address = $user->addresses()->where('is_default', true)->first();
        if ($address) {
            $data['location'] = [
                'city' => $address->city,
                'state' => $address->state?->name ?? null,
            ];
        }

        // Performance indicators (for upline to assess team)
        if (in_array($relationship, ['direct', 'upline'])) {
            $thisMonth = now()->startOfMonth();
            $data['performance'] = [
                'referrals_this_month' => $user->children()
                    ->where('created_at', '>=', $thisMonth)
                    ->count(),
                'is_active' => $user->children()
                    ->where('created_at', '>=', now()->subDays(30))
                    ->exists() || $activeSubscription !== null,
            ];
        }

        return $data;
    }

    /**
     * Get direct referrals (children) of a user.
     * Limited info for privacy.
     */
    private function getDirectReferrals(User $user, User $viewer): array
    {
        $children = $user->children()
            ->with(['subscriptions' => fn ($q) => $q->where('status', 'active')->with('stage')])
            ->withCount('children as direct_count')
            ->get();

        return [
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
            ],
            'referrals' => $children->map(fn (User $child) => [
                'uuid' => $child->uuid,
                'name' => $child->name,
                'avatar_url' => $child->getFirstMediaUrl('avatar'),
                'referral_code' => $child->referral_code,
                'joined_at' => $child->created_at->format('Y-m-d'),
                'is_subscribed' => $child->subscriptions->isNotEmpty(),
                'stage' => $child->subscriptions->first()?->stage?->name ?? 'None',
                'direct_count' => $child->direct_count,
                'can_view_team' => $child->direct_count > 0,
            ]),
            'total' => $children->count(),
        ];
    }
}
