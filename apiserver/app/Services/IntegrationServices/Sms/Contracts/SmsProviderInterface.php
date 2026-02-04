<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Sms\Contracts;

use App\Models\Integration;
use App\Services\IntegrationServices\Sms\DTOs\BalanceInfo;
use App\Services\IntegrationServices\Sms\DTOs\DeliveryReport;
use App\Services\IntegrationServices\Sms\DTOs\SmsRequest;
use App\Services\IntegrationServices\Sms\DTOs\SmsResponse;

/**
 * SMS Provider Interface - Contract for all SMS providers.
 *
 * Implementations must support:
 * - Single & bulk SMS sending
 * - Balance checking
 * - Delivery report fetching
 * - Service health checking
 */
interface SmsProviderInterface
{
    /**
     * Get the provider slug/identifier.
     */
    public function getSlug(): string;

    /**
     * Get human-readable provider name.
     */
    public function getName(): string;

    /**
     * Check if the provider is properly configured.
     */
    public function isConfigured(): bool;

    /**
     * Check if the provider service is available/healthy.
     */
    public function isServiceable(): bool;

    /**
     * Send SMS using the new DTO-based approach.
     */
    public function send(SmsRequest $request): SmsResponse;

    /**
     * Send SMS to single or multiple recipients (legacy support).
     *
     * @param  array<string>  $recipients  Phone numbers in E.164 format
     * @param  string  $message  Message content
     * @return array{success: bool, message: string, data?: array<string, mixed>}
     *
     * @deprecated Use send(SmsRequest) instead
     */
    public function sendLegacy(array $recipients, string $message): array;

    /**
     * Send DLT SMS using template.
     *
     * @param  array<string>  $recipients  Phone numbers
     * @param  string  $messageId  DLT message ID (6-digit for Fast2SMS)
     * @param  string  $variablesValues  Pipe-separated variable values
     * @param  string|null  $senderId  Optional sender ID override
     */
    public function sendDlt(
        array $recipients,
        string $messageId,
        string $variablesValues,
        ?string $senderId = null,
    ): SmsResponse;

    /**
     * Check wallet balance.
     */
    public function getBalance(): BalanceInfo;

    /**
     * Check if can send specific number of SMS (balance check).
     */
    public function canSend(int $count = 1): bool;

    /**
     * Get delivery report for a message.
     */
    public function getDeliveryReport(string $requestId): DeliveryReport;

    /**
     * Get delivery reports for multiple messages.
     *
     * @param  array<string>  $requestIds
     * @return array<string, \App\Services\IntegrationServices\Sms\DTOs\DeliveryReport>
     */
    public function getDeliveryReports(array $requestIds): array;

    /**
     * Get SMS logs/analytics from provider.
     *
     * @param  \DateTimeInterface|null  $from  Start date (default: 3 days ago)
     * @param  \DateTimeInterface|null  $to  End date (default: now)
     * @return array<string, mixed>
     */
    public function getLogs(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): array;

    /**
     * Set the provider model (for database-backed configuration).
     */
    public function setIntegration(Integration $integration): void;

    /**
     * Get the provider model.
     */
    public function getIntegration(): ?Integration;
}
