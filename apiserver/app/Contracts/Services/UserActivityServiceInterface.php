<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

interface UserActivityServiceInterface
{
    /**
     * Log generic activity
     */
    public function logActivity(
        User $user,
        string $description,
        ?string $event = null,
        array $properties = []
    ): Activity;

    /**
     * Log page view
     */
    public function logPageView(User $user, string $page, array $meta = []): Activity;

    /**
     * Log user action
     */
    public function logAction(User $user, string $action, array $meta = []): Activity;

    /**
     * Log login event
     */
    public function logLogin(User $user, array $meta = []): Activity;

    /**
     * Log logout event
     */
    public function logLogout(User $user): Activity;

    /**
     * Log profile update
     */
    public function logProfileUpdate(User $user, array $changes): Activity;

    /**
     * Log subscription event
     */
    public function logSubscription(User $user, string $action, array $meta = []): Activity;

    /**
     * Log wallet view
     */
    public function logWalletView(User $user): Activity;

    /**
     * Log referral share
     */
    public function logReferralShare(User $user, string $platform): Activity;

    /**
     * Log client-side event
     */
    public function logClientEvent(User $user, string $event, array $data = []): Activity;

    /**
     * Get activities for user
     */
    public function getActivitiesForUser(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get all activities with filters
     *
     * @param array{
     *   user_id?: int,
     *   event?: string,
     *   from?: string,
     *   to?: string
     * } $filters
     */
    public function getAllActivities(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
