<?php

declare(strict_types=1);

namespace App\Services\UserServices;

use App\Contracts\Services\UserPermissionServiceInterface;
use App\Models\User;

/**
 * UserPermissionService
 *
 * Provides static permissions based on user state (subscription, type, KYC, etc.).
 * No database tables needed - permissions are derived from existing user data.
 * Output format is compatible with Spatie role/permission for frontend consistency.
 */
final class UserPermissionService implements UserPermissionServiceInterface
{
    private User $user;

    private array $permissions = [];

    // ========================================
    // Static Permission Definitions
    // ========================================

    /**
     * Base permissions available to all authenticated users.
     */
    private const BASE_PERMISSIONS = [
        'profile.view',
        'profile.edit',
        'dashboard.view',
        'notifications.view',
        'help.view',
        'career.view',
        'career.apply',
    ];

    /**
     * Permissions for users who completed onboarding.
     */
    private const ONBOARDED_PERMISSIONS = [
        'wallet.view',
        'wallet.receive',
        'addresses.manage',
        'subscription.view',
        'subscription.purchase',
    ];

    /**
     * Permissions for users with verified KYC.
     */
    private const KYC_VERIFIED_PERMISSIONS = [
        'wallet.send',
        'wallet.withdraw',
        'beneficiary.manage',
    ];

    /**
     * Permissions for users with active subscription (Member+).
     */
    private const SUBSCRIBED_PERMISSIONS = [
        'affiliate.view',
        'affiliate.tree',
        'affiliate.referrals',
        'commissions.view',
        'earnings.view',
        'network.view',
        'messages.view',
        'messages.send',
        'share.referral',
    ];

    /**
     * Permissions for Promoter stage and above.
     */
    private const PROMOTER_PERMISSIONS = [
        'affiliate.team_extended',
        'commissions.level',
    ];

    /**
     * Permissions for Mentor stage and above.
     */
    private const MENTOR_PERMISSIONS = [
        'affiliate.deep_tree',
        'team.management',
    ];

    /**
     * Permissions for Advisor stage.
     */
    private const ADVISOR_PERMISSIONS = [
        'affiliate.full_tree',
        'reports.advanced',
        'team.full_management',
    ];

    /**
     * Create service instance for a user.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->loadPermissions();
    }

    /**
     * Static factory method.
     */
    public static function for(User $user): self
    {
        return new self($user);
    }

    /**
     * Load all applicable permissions for the user.
     */
    private function loadPermissions(): void
    {
        // Start with base permissions
        $this->permissions = self::BASE_PERMISSIONS;

        // Add onboarding permissions if completed
        if ($this->user->onboarding_completed_at) {
            $this->permissions = array_merge($this->permissions, self::ONBOARDED_PERMISSIONS);
        }

        // Add KYC permissions if verified
        if ($this->hasVerifiedKyc()) {
            $this->permissions = array_merge($this->permissions, self::KYC_VERIFIED_PERMISSIONS);
        }

        // Add subscription-based permissions
        $this->addSubscriptionPermissions();

        // Ensure unique permissions
        $this->permissions = array_unique($this->permissions);
    }

    /**
     * Check if user has verified KYC.
     */
    private function hasVerifiedKyc(): bool
    {
        return $this->user->kyc()
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Add permissions based on subscription stage.
     */
    private function addSubscriptionPermissions(): void
    {
        $activeSubscription = $this->user->subscriptions()
            ->where('status', 'active')
            ->whereDate('expires_at', '>', now())
            ->with('stage')
            ->first();

        if (! $activeSubscription) {
            return;
        }

        // All subscribed users get these
        $this->permissions = array_merge($this->permissions, self::SUBSCRIBED_PERMISSIONS);

        // Get stage slug for permission mapping
        $stageSlug = $activeSubscription->stage?->slug ?? '';

        // Add stage-specific permissions
        $stagePermissions = match ($stageSlug) {
            'advisor' => array_merge(
                self::PROMOTER_PERMISSIONS,
                self::MENTOR_PERMISSIONS,
                self::ADVISOR_PERMISSIONS
            ),
            'mentor' => array_merge(
                self::PROMOTER_PERMISSIONS,
                self::MENTOR_PERMISSIONS
            ),
            'promoter' => self::PROMOTER_PERMISSIONS,
            default => [], // Member stage - base subscribed permissions only
        };

        $this->permissions = array_merge($this->permissions, $stagePermissions);
    }

    // ========================================
    // Public API (Spatie-compatible format)
    // ========================================

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return count(array_intersect($permissions, $this->permissions)) > 0;
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return count(array_intersect($permissions, $this->permissions)) === count($permissions);
    }

    /**
     * Get all user permissions.
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Get permissions as array for API response.
     * Format compatible with Spatie permission output.
     */
    public function toArray(): array
    {
        return [
            'permissions' => $this->permissions,
            'can' => $this->getPermissionFlags(),
        ];
    }

    /**
     * Get permission flags for common checks.
     * Useful for quick frontend checks.
     */
    public function getPermissionFlags(): array
    {
        return [
            // Wallet
            'wallet_view' => $this->hasPermission('wallet.view'),
            'wallet_send' => $this->hasPermission('wallet.send'),
            'wallet_withdraw' => $this->hasPermission('wallet.withdraw'),

            // Affiliate/Network
            'affiliate_view' => $this->hasPermission('affiliate.view'),
            'affiliate_tree' => $this->hasPermission('affiliate.tree'),
            'network_view' => $this->hasPermission('network.view'),
            'commissions_view' => $this->hasPermission('commissions.view'),

            // Messaging
            'messages_view' => $this->hasPermission('messages.view'),
            'messages_send' => $this->hasPermission('messages.send'),

            // Sharing
            'share_referral' => $this->hasPermission('share.referral'),

            // Subscription
            'subscription_view' => $this->hasPermission('subscription.view'),
            'subscription_purchase' => $this->hasPermission('subscription.purchase'),

            // KYC
            'beneficiary_manage' => $this->hasPermission('beneficiary.manage'),

            // Computed flags
            'is_subscribed' => $this->hasPermission('affiliate.view'),
            'is_kyc_verified' => $this->hasPermission('wallet.send'),
            'is_onboarded' => $this->hasPermission('wallet.view'),
        ];
    }
}
