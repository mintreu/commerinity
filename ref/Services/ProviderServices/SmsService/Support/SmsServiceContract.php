<?php

namespace App\Services\ProviderServices\SmsService\Support;

interface SmsServiceContract
{
    public function getName(): string;

    public function send(array $numbers, string $message, bool $flash = false, string $lang = 'english');

    public function getBalance(): ?string;

    public function sendOtp(string $phoneNumber): bool;

    public function validateOtp(string $phoneNumber, int $token): array;
}
