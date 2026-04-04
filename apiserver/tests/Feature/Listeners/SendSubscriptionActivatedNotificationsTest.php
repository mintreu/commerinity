<?php

declare(strict_types=1);

use App\Events\Affiliate\SubscriptionActivated;
use App\Listeners\Notification\SendSubscriptionActivatedNotifications;
use App\Models\Membership\Stage;
use App\Models\Membership\UserSubscription;
use App\Models\User;
use App\Notifications\SubscriptionActivatedNotification;
use App\Services\IntegrationServices\Sms\DTOs\SmsResponse;
use App\Contracts\Services\NotificationSmsSenderInterface;
use Database\Factories\Membership\StageFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    StageFactory::resetCounter();
});

it('sends notification and sms when the gateway has balance', function () {
    Notification::fake();

    $stage = Stage::factory()->withLevels()->create();
    $stage->load('levels');
    $level = $stage->levels->first();
    expect($level)->not->toBeNull();

    $user = User::factory()->create(['mobile' => '+919876543210']);
    $subscription = UserSubscription::factory()
        ->forStage($stage)
        ->forUser($user)
        ->create([
            'level_id' => $level->id,
            'current_level_id' => $level->id,
            'status' => UserSubscription::STATUS_ACTIVE,
            'is_paid' => true,
        ]);

    $smsService = mock(NotificationSmsSenderInterface::class);
    $smsService->shouldReceive('canSend')->with(1)->andReturn(true);
    $smsService->shouldReceive('sendTemplate')
        ->once()
        ->with(
            $user->mobile,
            'subscription-status',
            \Mockery::on(fn (array $variables): bool => isset($variables['status'], $variables['plan'], $variables['reference'])),
            'transactional',
            $user->id
        )
        ->andReturn(SmsResponse::success());

    $listener = new SendSubscriptionActivatedNotifications($smsService);
    $listener->handle(new SubscriptionActivated($subscription, collect()));

    Notification::assertSentTo($user, SubscriptionActivatedNotification::class);
});

it('skips sms when balance is insufficient', function () {
    Notification::fake();

    $stage = Stage::factory()->withLevels()->create();
    $stage->load('levels');
    $level = $stage->levels->first();
    expect($level)->not->toBeNull();

    $user = User::factory()->create(['mobile' => '+919876543210']);
    $subscription = UserSubscription::factory()
        ->forStage($stage)
        ->forUser($user)
        ->create([
            'level_id' => $level->id,
            'current_level_id' => $level->id,
            'status' => UserSubscription::STATUS_ACTIVE,
            'is_paid' => true,
        ]);

    $smsService = mock(NotificationSmsSenderInterface::class);
    $smsService->shouldReceive('canSend')->with(1)->andReturn(false);
    $smsService->shouldReceive('sendTemplate')->never();

    $listener = new SendSubscriptionActivatedNotifications($smsService);
    $listener->handle(new SubscriptionActivated($subscription, collect()));

    Notification::assertSentTo($user, SubscriptionActivatedNotification::class);
});
