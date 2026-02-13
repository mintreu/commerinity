<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Contracts\Services\NotificationSmsSenderInterface;
use App\Services\IntegrationServices\Sms\DTOs\SmsResponse;
use App\Services\IntegrationServices\Sms\SmsService;

final class NotificationSmsSender implements NotificationSmsSenderInterface
{
    public function __construct(private readonly SmsService $smsService) {}

    public function canSend(int $count = 1): bool
    {
        return $this->smsService->canSend($count);
    }

    public function sendSingle(string $phone, string $message, string $type = 'transactional', ?int $userId = null): SmsResponse
    {
        return $this->smsService->sendSingle($phone, $message, $type, $userId);
    }
}
