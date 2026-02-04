<?php

declare(strict_types=1);

namespace App\Services\IntegrationServices\Sms\Providers;

use App\Models\Integration;
use App\Services\IntegrationServices\Sms\Contracts\SmsProviderInterface;
use App\Services\IntegrationServices\Sms\DTOs\BalanceInfo;
use App\Services\IntegrationServices\Sms\DTOs\DeliveryReport;
use App\Services\IntegrationServices\Sms\DTOs\SmsRequest;
use App\Services\IntegrationServices\Sms\DTOs\SmsResponse;
use Illuminate\Support\Facades\Log;

/**
 * Log SMS Provider - Development/testing provider.
 *
 * Logs SMS messages instead of sending them.
 * Always reports success, unlimited balance, instant delivery.
 */
final class LogSmsProvider implements SmsProviderInterface
{
    private ?Integration $integration = null;

    public function getSlug(): string
    {
        return 'log';
    }

    public function getName(): string
    {
        return 'Log Provider (Testing)';
    }

    public function isConfigured(): bool
    {
        return true; // Always configured
    }

    public function isServiceable(): bool
    {
        return true; // Always serviceable
    }

    public function send(SmsRequest $request): SmsResponse
    {
        foreach ($request->recipients as $recipient) {
            Log::channel('sms')->info('SMS sent (log provider)', [
                'to' => $recipient,
                'message' => $request->message,
                'type' => $request->type,
                'template' => $request->templateSlug,
                'variables' => $request->variables,
                'provider' => $this->getSlug(),
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        return SmsResponse::success(
            message: 'SMS logged successfully (testing mode)',
            requestId: 'LOG_'.uniqid(),
            messageId: 'MSG_'.uniqid(),
            cost: 0.0,
            providerData: [
                'recipients' => $request->recipients,
                'count' => $request->getRecipientCount(),
            ],
        );
    }

    public function sendLegacy(array $recipients, string $message): array
    {
        foreach ($recipients as $recipient) {
            Log::channel('sms')->info('SMS sent', [
                'to' => $recipient,
                'message' => $message,
                'provider' => $this->getSlug(),
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        return [
            'success' => true,
            'message' => 'SMS logged successfully (testing mode)',
            'data' => [
                'recipients' => $recipients,
                'count' => count($recipients),
            ],
        ];
    }

    public function sendDlt(
        array $recipients,
        string $messageId,
        string $variablesValues,
        ?string $senderId = null,
    ): SmsResponse {
        Log::channel('sms')->info('DLT SMS sent (log provider)', [
            'recipients' => $recipients,
            'message_id' => $messageId,
            'variables' => $variablesValues,
            'sender_id' => $senderId,
            'provider' => $this->getSlug(),
        ]);

        return SmsResponse::success(
            message: 'DLT SMS logged successfully (testing mode)',
            requestId: 'LOG_DLT_'.uniqid(),
            messageId: $messageId,
        );
    }

    public function getBalance(): BalanceInfo
    {
        // Log provider has unlimited balance
        return BalanceInfo::fromBalance(
            balance: 999999.99,
            perSmsCost: 0.0,
            threshold: 0.0,
        );
    }

    public function canSend(int $count = 1): bool
    {
        return true; // Always can send
    }

    public function getDeliveryReport(string $requestId): DeliveryReport
    {
        // Log provider always reports delivered
        return new DeliveryReport(
            success: true,
            requestId: $requestId,
            messageId: 'MSG_'.uniqid(),
            status: DeliveryReport::STATUS_DELIVERED,
            deliveredAt: now()->toIso8601String(),
        );
    }

    public function getDeliveryReports(array $requestIds): array
    {
        $reports = [];

        foreach ($requestIds as $requestId) {
            $reports[$requestId] = $this->getDeliveryReport($requestId);
        }

        return $reports;
    }

    public function getLogs(?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): array
    {
        return [
            'success' => true,
            'logs' => [],
            'total' => 0,
            'message' => 'Log provider does not store logs',
        ];
    }

    public function setIntegration(Integration $integration): void
    {
        $this->integration = $integration;
    }

    public function getIntegration(): ?Integration
    {
        return $this->integration;
    }
}
