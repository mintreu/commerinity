<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\User;
use App\Services\Sms\DTOs\SmsResponse;

interface SmsServiceInterface
{
    /**
     * Send SMS message
     */
    public function send(string $to, string $message, ?string $templateId = null): SmsResponse;

    /**
     * Send OTP SMS
     */
    public function sendOtp(string $to, string $otp): SmsResponse;

    /**
     * Send welcome SMS
     */
    public function sendWelcome(User $user): SmsResponse;

    /**
     * Send single SMS
     */
    public function sendSingle(string $to, string $message): SmsResponse;

    /**
     * Send bulk SMS
     *
     * @param  array<string>  $recipients
     * @return array<SmsResponse>
     */
    public function sendBulk(array $recipients, string $message): array;

    /**
     * Get balance for active provider
     */
    public function getBalance(): ?float;

    /**
     * Get balances for all providers
     *
     * @return array<string, float|null>
     */
    public function getAllBalances(): array;

    /**
     * Check if SMS can be sent
     */
    public function canSend(): bool;

    /**
     * Get delivery report for message
     *
     * @return array{
     *   status: string,
     *   delivered_at: ?string,
     *   error: ?string
     * }|null
     */
    public function getDeliveryReport(string $messageId): ?array;

    /**
     * Get SMS analytics
     *
     * @return array{
     *   total_sent: int,
     *   delivered: int,
     *   failed: int,
     *   pending: int,
     *   cost: int
     * }
     */
    public function getAnalytics(?string $period = null): array;

    /**
     * Get monthly projection
     *
     * @return array{
     *   projected_count: int,
     *   projected_cost: int,
     *   current_balance: float
     * }
     */
    public function getMonthlyProjection(): array;

    /**
     * Get expense projections for all providers
     *
     * @return array<string, array>
     */
    public function getAllExpenseProjections(): array;

    /**
     * Sync balance from provider
     */
    public function syncBalance(): void;

    /**
     * Get active provider slug
     */
    public function getActiveProviderSlug(): ?string;
}
