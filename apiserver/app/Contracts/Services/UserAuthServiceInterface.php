<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use Illuminate\Http\Request;
use Laravel\Sanctum\NewAccessToken;

interface UserAuthServiceInterface
{
    /**
     * Authenticate user with email/mobile and password
     *
     * @throws \App\Exceptions\Auth\UserBannedException
     * @throws \App\Exceptions\Auth\UserSuspendedException
     */
    public function loginWithPassword(string $credential, string $password, string $tokenName = 'api'): ?NewAccessToken;

    /**
     * Authenticate user with email/mobile and OTP
     *
     * @throws \App\Exceptions\Auth\UserBannedException
     * @throws \App\Exceptions\Auth\UserSuspendedException
     */
    public function loginWithOtp(string $credential, string $otp, string $tokenName = 'api'): ?NewAccessToken;

    /**
     * Logout user from current device
     */
    public function logout(Request $request): bool;

    /**
     * Logout user from all devices
     */
    public function logoutAll(Request $request): bool;

    /**
     * Logout user from all devices of specific type
     */
    public function logoutDeviceType(Request $request, string $deviceType): bool;
}
