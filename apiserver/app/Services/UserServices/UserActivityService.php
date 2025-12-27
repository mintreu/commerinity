<?php

declare(strict_types=1);

namespace App\Services\UserServices;

use App\Contracts\Services\UserActivityServiceInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/**
 * UserActivityService
 *
 * Wrapper service for tracking user activities using spatie/laravel-activitylog.
 * Tracks activities from client side with IP, location, user-agent, and other metadata.
 * Activity is only visible to admin, not to users.
 */
final class UserActivityService implements UserActivityServiceInterface
{
    private const LOG_NAME = 'user-activity';

    public function __construct(
        private readonly ?Request $request = null,
    ) {}

    /**
     * Create a new instance of the service.
     */
    public static function make(?Request $request = null): self
    {
        return new self($request ?? app(Request::class));
    }

    /**
     * Log a user activity from client side.
     */
    public function logActivity(
        User $user,
        string $event,
        string $description,
        ?array $properties = null,
    ): Activity {
        $activity = activity(self::LOG_NAME)
            ->causedBy($user)
            ->withProperties($this->buildProperties($properties))
            ->event($event)
            ->log($description);

        return $activity;
    }

    /**
     * Log a page view activity.
     */
    public function logPageView(
        User $user,
        string $pagePath,
        string $pageTitle,
        ?string $referrer = null,
    ): Activity {
        return $this->logActivity(
            user: $user,
            event: 'page_view',
            description: "Viewed page: {$pageTitle}",
            properties: [
                'page_path' => $pagePath,
                'page_title' => $pageTitle,
                'referrer' => $referrer,
            ],
        );
    }

    /**
     * Log a user action (button click, form submit, etc.).
     */
    public function logAction(
        User $user,
        string $action,
        string $target,
        ?array $data = null,
    ): Activity {
        return $this->logActivity(
            user: $user,
            event: 'action',
            description: "Action: {$action} on {$target}",
            properties: [
                'action' => $action,
                'target' => $target,
                'data' => $data,
            ],
        );
    }

    /**
     * Log a login activity.
     */
    public function logLogin(User $user, string $method = 'password'): Activity
    {
        return $this->logActivity(
            user: $user,
            event: 'login',
            description: "User logged in via {$method}",
            properties: [
                'method' => $method,
            ],
        );
    }

    /**
     * Log a logout activity.
     */
    public function logLogout(User $user): Activity
    {
        return $this->logActivity(
            user: $user,
            event: 'logout',
            description: 'User logged out',
        );
    }

    /**
     * Log a profile update.
     */
    public function logProfileUpdate(User $user, array $changedFields): Activity
    {
        return $this->logActivity(
            user: $user,
            event: 'profile_update',
            description: 'User updated profile',
            properties: [
                'changed_fields' => $changedFields,
            ],
        );
    }

    /**
     * Log a subscription activity.
     */
    public function logSubscription(User $user, string $action, ?string $planName = null): Activity
    {
        return $this->logActivity(
            user: $user,
            event: 'subscription',
            description: "Subscription {$action}".($planName ? " for plan: {$planName}" : ''),
            properties: [
                'action' => $action,
                'plan' => $planName,
            ],
        );
    }

    /**
     * Log a wallet transaction view.
     */
    public function logWalletView(User $user): Activity
    {
        return $this->logActivity(
            user: $user,
            event: 'wallet_view',
            description: 'User viewed wallet',
        );
    }

    /**
     * Log a referral share activity.
     */
    public function logReferralShare(User $user, string $platform): Activity
    {
        return $this->logActivity(
            user: $user,
            event: 'referral_share',
            description: "Shared referral link via {$platform}",
            properties: [
                'platform' => $platform,
            ],
        );
    }

    /**
     * Log a generic client-side event.
     */
    public function logClientEvent(
        User $user,
        string $eventName,
        ?array $eventData = null,
    ): Activity {
        return $this->logActivity(
            user: $user,
            event: $eventName,
            description: "Client event: {$eventName}",
            properties: [
                'event_data' => $eventData,
            ],
        );
    }

    /**
     * Build properties array with device/request metadata.
     */
    private function buildProperties(?array $additionalProperties = null): array
    {
        $properties = [
            'ip' => $this->getIpAddress(),
            'user_agent' => $this->getUserAgent(),
            'device' => $this->parseDeviceInfo(),
            'timestamp' => now()->toIso8601String(),
        ];

        // Add location data if available from request
        if ($this->request?->has('location')) {
            $properties['location'] = $this->request->input('location');
        }

        // Add screen/viewport info if provided
        if ($this->request?->has('screen')) {
            $properties['screen'] = $this->request->input('screen');
        }

        // Merge additional properties
        if ($additionalProperties !== null) {
            $properties = array_merge($properties, $additionalProperties);
        }

        return $properties;
    }

    /**
     * Get client IP address (handles proxies).
     */
    private function getIpAddress(): ?string
    {
        if (! $this->request) {
            return null;
        }

        // Check for forwarded IP (behind proxy/load balancer)
        $forwardedFor = $this->request->header('X-Forwarded-For');
        if ($forwardedFor) {
            $ips = explode(',', $forwardedFor);

            return trim($ips[0]);
        }

        return $this->request->ip();
    }

    /**
     * Get user agent string.
     */
    private function getUserAgent(): ?string
    {
        return $this->request?->userAgent();
    }

    /**
     * Parse device info from user agent.
     */
    private function parseDeviceInfo(): array
    {
        $userAgent = $this->getUserAgent() ?? '';

        return [
            'is_mobile' => $this->isMobile($userAgent),
            'is_tablet' => $this->isTablet($userAgent),
            'is_desktop' => ! $this->isMobile($userAgent) && ! $this->isTablet($userAgent),
            'browser' => $this->detectBrowser($userAgent),
            'os' => $this->detectOS($userAgent),
        ];
    }

    /**
     * Check if device is mobile.
     */
    private function isMobile(string $userAgent): bool
    {
        return (bool) preg_match(
            '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',
            $userAgent
        );
    }

    /**
     * Check if device is tablet.
     */
    private function isTablet(string $userAgent): bool
    {
        return (bool) preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $userAgent);
    }

    /**
     * Detect browser from user agent.
     */
    private function detectBrowser(string $userAgent): string
    {
        if (preg_match('/MSIE|Trident/i', $userAgent)) {
            return 'Internet Explorer';
        }
        if (preg_match('/Edge/i', $userAgent)) {
            return 'Edge';
        }
        if (preg_match('/Edg/i', $userAgent)) {
            return 'Edge Chromium';
        }
        if (preg_match('/Firefox/i', $userAgent)) {
            return 'Firefox';
        }
        if (preg_match('/Chrome/i', $userAgent) && ! preg_match('/Chromium/i', $userAgent)) {
            return 'Chrome';
        }
        if (preg_match('/Safari/i', $userAgent) && ! preg_match('/Chrome/i', $userAgent)) {
            return 'Safari';
        }
        if (preg_match('/Opera|OPR/i', $userAgent)) {
            return 'Opera';
        }

        return 'Unknown';
    }

    /**
     * Detect OS from user agent.
     */
    private function detectOS(string $userAgent): string
    {
        if (preg_match('/windows nt 10/i', $userAgent)) {
            return 'Windows 10/11';
        }
        if (preg_match('/windows nt 6.3/i', $userAgent)) {
            return 'Windows 8.1';
        }
        if (preg_match('/windows nt 6.2/i', $userAgent)) {
            return 'Windows 8';
        }
        if (preg_match('/windows nt 6.1/i', $userAgent)) {
            return 'Windows 7';
        }
        if (preg_match('/macintosh|mac os x/i', $userAgent)) {
            return 'macOS';
        }
        if (preg_match('/iphone/i', $userAgent)) {
            return 'iOS (iPhone)';
        }
        if (preg_match('/ipad/i', $userAgent)) {
            return 'iOS (iPad)';
        }
        if (preg_match('/android/i', $userAgent)) {
            return 'Android';
        }
        if (preg_match('/linux/i', $userAgent)) {
            return 'Linux';
        }

        return 'Unknown';
    }

    /**
     * Get activities for a specific user.
     */
    public static function getActivitiesForUser(User $user, int $limit = 50): Collection
    {
        return Activity::query()
            ->where('log_name', self::LOG_NAME)
            ->where('causer_type', User::class)
            ->where('causer_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all activities (admin view).
     */
    public static function getAllActivities(int $limit = 100): Collection
    {
        return Activity::query()
            ->where('log_name', self::LOG_NAME)
            ->with('causer')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
