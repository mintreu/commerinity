<?php

declare(strict_types=1);

namespace App\Services\UserServices;

use App\Casts\UserStatusCast;
use App\Exceptions\Auth\UserBannedException;
use App\Exceptions\Auth\UserSuspendedException;
use App\Helpers\OtpManager;
use App\Models\User;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;

final class UserAuthService implements \App\Contracts\Services\UserAuthServiceInterface
{
    private readonly OtpManager $otpManager;

    public function __construct(
        private readonly CacheContract $cache,
        private readonly Hasher $hasher,
    ) {
        $this->otpManager = new OtpManager(
            $this->cache,
            $this->hasher,
            (bool) config('services.sms.options.demo_mode', false)
        );
    }

    /**
     * Authenticate user with email/mobile and password
     *
     * @throws UserBannedException
     * @throws UserSuspendedException
     */
    public function loginWithPassword(string $credential, string $password, string $tokenName = 'api'): ?NewAccessToken
    {
        $user = $this->findUserByCredential($credential);

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        $this->checkUserStatus($user);

        return $user->createToken($tokenName);
    }

    /**
     * Authenticate user with email/mobile and OTP
     *
     * @throws UserBannedException
     * @throws UserSuspendedException
     */
    public function loginWithOtp(string $credential, string $otp, string $tokenName = 'api'): ?NewAccessToken
    {
        $user = $this->findUserByCredential($credential);

        if (! $user) {
            return null;
        }

        $this->checkUserStatus($user);

        if (! $this->otpManager->verify($credential, $otp)) {
            return null;
        }

        $this->otpManager->clear($credential);

        return $user->createToken($tokenName);
    }

    /**
     * Logout user from current device
     */
    public function logout(Request $request): bool
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        // Invalidate session if exists
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return true;
    }

    /**
     * Logout user from all devices
     */
    public function logoutAll(Request $request): bool
    {
        $request->user()->tokens()->delete();

        // Invalidate session if exists
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return true;
    }

    /**
     * Logout user from all devices of specific type (android, ios, nuxt)
     */
    public function logoutDeviceType(Request $request, string $deviceType): bool
    {
        // Delete all tokens with matching name (device type)
        $request->user()->tokens()->where('name', $deviceType)->delete();

        // Invalidate session if exists
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return true;
    }

    /**
     * Check if user account is active and throw exception if not
     *
     * @throws UserBannedException
     * @throws UserSuspendedException
     */
    private function checkUserStatus(User $user): void
    {
        if ($user->status === UserStatusCast::BANNED) {
            throw new UserBannedException;
        }

        if ($user->status === UserStatusCast::SUSPENDED) {
            throw new UserSuspendedException;
        }
    }

    /**
     * Find user by email or mobile
     */
    private function findUserByCredential(string $credential): ?User
    {
        // Check if credential contains @ (email)
        if (str_contains($credential, '@')) {
            return User::where('email', $credential)->first();
        }

        // Otherwise treat as mobile
        return User::where('mobile', $credential)->first();
    }
}
