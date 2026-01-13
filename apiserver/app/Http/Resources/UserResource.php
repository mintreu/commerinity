<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Casts\KycStatusCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Http\Resources\ImageResource;
use App\Services\UserServices\UserPermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * This is the primary user data source for frontend personalization.
     * All UI personalization decisions should be based on this response.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Identity
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,

            // Verification Status
            'email_verified' => ! is_null($this->email_verified_at),
            'mobile_verified' => $this->hasVerifiedMobile(),

            // Affiliate Tree
            'referral_code' => $this->referral_code,
            'parent' => $this->when($this->parent, fn () => [
                'uuid' => $this->parent->uuid,
                'name' => $this->parent->name,
            ]),
            'hasParent' => ! is_null($this->parent_id),

            // Profile
            'gender' => $this->gender?->value,
            'dob' => $this->dob?->format('Y-m-d'),
            'bio' => $this->bio,
            //'avatar' => $this->getFirstMedia('avatar') ? (new ImageResource($this->getFirstMedia('avatar')))->toArray(request()) : null,
            'avatar' => $this->getFirstMediaUrl('avatarImage'),

            // Type & Status (CRITICAL for personalization)
            'type' => $this->type->value,
            'status' => $this->status->value,
            'onboarded' => $this->onboarded,

            // Membership (when available)
            'hasLevel' => ! is_null($this->level_id),
            'level_id' => $this->level_id,

            // Genealogy Data (for Affiliate users)
            'genealogy' => $this->when($this->genealogy, fn () => [
                'personal_pv' => $this->genealogy->personal_pv,
                'team_pv' => $this->genealogy->team_pv,
                'direct_count' => $this->genealogy->direct_count,
                'active_direct_count' => $this->genealogy->active_direct_count,
                'total_team_count' => $this->genealogy->total_team_count,
                'personal_sales' => $this->genealogy->personal_sales,
                'total_team_sales' => $this->genealogy->total_team_sales,
                'level_1_count' => $this->genealogy->level_1_count,
                'level_2_count' => $this->genealogy->level_2_count,
                'level_3_count' => $this->genealogy->level_3_count,
                'level_4_count' => $this->genealogy->level_4_count,
                'stage' => $this->when($this->genealogy->currentStage, fn () => [
                    'uuid' => $this->genealogy->currentStage->uuid,
                    'name' => $this->genealogy->currentStage->name,
                    'pv' => $this->genealogy->currentStage->pv,
                    'bv' => $this->genealogy->currentStage->bv,
                ]),
                'level' => $this->when($this->genealogy->currentLevel, fn () => [
                    'uuid' => $this->genealogy->currentLevel->uuid,
                    'name' => $this->genealogy->currentLevel->name,
                    'full_name' => $this->genealogy->currentLevel->full_name,
                    'global_rank' => $this->genealogy->currentLevel->global_rank,
                    'level_number' => $this->genealogy->currentLevel->level_number,
                    'badge_icon' => $this->genealogy->currentLevel->badge_icon,
                    'badge_color' => $this->genealogy->currentLevel->badge_color,
                ]),
            ]),

            // KYC Status
            'kyc_status' => $this->kyc?->status->value ?? KycStatusCast::NOT_SUBMITTED->value,

            // Team Summary (for Affiliate users) - computed on demand
            'team_summary' => $this->when($this->isAffiliateUser(), fn () => [
                'direct_count' => $this->children()->count(),
                'active_count' => $this->children()
                    ->whereIn('type', [UserTypeCast::MEMBER, UserTypeCast::PROMOTER])
                    ->count(),
            ]),

            // Permissions from UserPermissionService
            // All site permissions derived from user state (subscription, KYC, etc.)
            ...$this->getPermissionsData(),

            // Legacy permissions (for backward compatibility)
            'permissions' => [
                'can_withdraw' => $this->canWithdraw(),
                'can_refer' => $this->canRefer(),
                'can_access_affiliate' => $this->canAccessAffiliate(),
                'can_access_team' => $this->canAccessTeam(),
            ],

            // Feature Flags (for UI rendering)
            'features' => [
                'show_wallet' => $this->shouldShowWallet(),
                'show_network' => $this->isAffiliateUser(),
                'show_earnings' => $this->isAffiliateUser(),
                'show_team' => $this->canAccessTeam(),
                'show_training' => $this->canAccessTraining(),
                'show_upgrade_prompt' => $this->shouldShowUpgradePrompt(),
            ],
        ];
    }

    /**
     * Check if user is an Affiliate participant (Member, Promoter, or higher)
     */
    private function isAffiliateUser(): bool
    {
        return in_array($this->type, [
            UserTypeCast::MEMBER,
            UserTypeCast::PROMOTER,
            UserTypeCast::ADVISOR,
            UserTypeCast::MENTOR,
        ], true);
    }

    /**
     * Check if user can withdraw funds
     */
    private function canWithdraw(): bool
    {
        return $this->isAffiliateUser()
            && $this->status === UserStatusCast::ACTIVE
            && $this->hasApprovedKyc();
    }

    /**
     * Check if user can refer others
     */
    private function canRefer(): bool
    {
        return $this->status === UserStatusCast::ACTIVE;
    }

    /**
     * Check if user can access Affiliate features
     */
    private function canAccessAffiliate(): bool
    {
        return $this->isAffiliateUser() && $this->status === UserStatusCast::ACTIVE;
    }

    /**
     * Check if user can access team management
     */
    private function canAccessTeam(): bool
    {
        return in_array($this->type, [
            UserTypeCast::PROMOTER,
            UserTypeCast::ADVISOR,
            UserTypeCast::MENTOR,
        ], true);
    }

    /**
     * Check if user can access training features
     */
    private function canAccessTraining(): bool
    {
        return in_array($this->type, [
            UserTypeCast::ADVISOR,
            UserTypeCast::MENTOR,
        ], true);
    }

    /**
     * Check if wallet section should be shown
     */
    private function shouldShowWallet(): bool
    {
        return $this->isAffiliateUser();
    }

    /**
     * Check if upgrade prompt should be shown
     */
    private function shouldShowUpgradePrompt(): bool
    {
        return $this->type === UserTypeCast::REGULAR
            && $this->status === UserStatusCast::ACTIVE;
    }

    /**
     * Get permissions data from UserPermissionService.
     * Returns permissions array and 'can' flags for frontend.
     */
    private function getPermissionsData(): array
    {
        $permissionService = UserPermissionService::for($this->resource);

        return [
            'all_permissions' => $permissionService->getPermissions(),
            'can' => $permissionService->getPermissionFlags(),
        ];
    }
}
