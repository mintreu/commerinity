<?php

declare(strict_types=1);

namespace App\Contracts\Helpers;

interface OtpManagerInterface
{
    /**
     * Generate and store OTP for credential
     *
     * @throws \RuntimeException if rate limited
     */
    public function generate(string $credential): int;

    /**
     * Verify OTP for credential
     *
     * @throws \RuntimeException if max attempts exceeded
     */
    public function verify(string $credential, string $otp): bool;

    /**
     * Clear OTP for credential
     */
    public function clear(string $credential): void;

    /**
     * Check if OTP exists for credential
     */
    public function exists(string $credential): bool;

    /**
     * Get remaining attempts for credential
     */
    public function getRemainingAttempts(string $credential): int;

    /**
     * Get cooldown seconds until next OTP can be sent
     */
    public function getCooldownSeconds(string $credential): int;

    /**
     * Check if in demo mode
     */
    public function isDemoMode(): bool;
}
