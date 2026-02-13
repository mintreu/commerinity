<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Services\IntegrationServices\Sms\DTOs\SmsResponse;

interface NotificationSmsSenderInterface
{
    public function canSend(int $count = 1): bool;

    public function sendSingle(string $phone, string $message, string $type = 'transactional', ?int $userId = null): SmsResponse;
}
