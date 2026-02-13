<?php

declare(strict_types=1);

use App\Casts\IntegrationTypeCast;
use App\Helpers\OtpManager;
use App\Models\Integration;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('email otp remains real even when sms integration is in demo mode', function () {
    Notification::fake();

    Integration::factory()->create([
        'name' => 'SMS Demo Gateway',
        'slug' => 'sms-demo',
        'type' => IntegrationTypeCast::SMS->value,
        'is_default' => true,
        'is_active' => true,
        'settings' => [
            'demo' => true,
        ],
    ]);

    $user = User::factory()->create([
        'email' => 'regular@demo.com',
    ]);

    /** @var OtpManager $otpManager */
    $otpManager = app(OtpManager::class);
    $result = $otpManager->sendOtp($user->email, OtpManager::CREDENTIAL_EMAIL, 'verification');

    expect($result['success'])->toBeTrue();
    expect($result['demo'])->toBeFalse();
    expect(array_key_exists('otp', $result))->toBeFalse();

    Notification::assertSentTo($user, OtpNotification::class);
});
