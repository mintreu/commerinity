<?php

declare(strict_types=1);

use App\Contracts\Services\NotificationSmsSenderInterface;
use App\Casts\JobApplicationStatusCast;
use App\Casts\RecruitmentStatusCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Events\PaymentCompleted;
use App\Models\Address;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\Recruitment;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\IntegrationServices\Sms\DTOs\SmsResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    \App\Models\Integration::factory()->cashfree()->create();
    app(\App\Services\IntegrationServices\Payment\PaymentService::class)->refreshProviders();

    Http::fake(['sandbox.cashfree.com/pg/orders' => Http::response([
        'cf_order_id' => 'cf_job_'.fake()->uuid(),
        'order_id' => '*',
        'payment_session_id' => 'session_'.fake()->uuid(),
        'order_status' => 'ACTIVE',
    ], 200)]);
});

test('job application apply sends user notification with application number', function () {
    Notification::fake();

    $smsService = mock(NotificationSmsSenderInterface::class);
    $smsService->shouldReceive('canSend')->with(1)->andReturn(true);
    $smsService->shouldReceive('sendTemplate')
        ->once()
        ->with(
            \Mockery::type('string'),
            'job-application-received',
            \Mockery::on(fn (array $variables): bool => isset($variables['name'], $variables['application_id'])),
            'transactional',
            \Mockery::type('int')
        )
        ->andReturn(SmsResponse::success());
    app()->instance(NotificationSmsSenderInterface::class, $smsService);

    $user = User::factory()->create(['mobile' => '+919876543210']);
    Address::factory()->forUser($user)->create();
    $recruitment = Recruitment::factory()->create([
        'status' => RecruitmentStatusCast::Published,
        'slug' => 'notif-free-job',
        'is_payable' => false,
        'fees' => 0,
    ]);

    $this->actingAs($user)
        ->postJson("/api/careers/{$recruitment->slug}/apply", [
            'guardian_name' => 'Guardian Name',
        ])
        ->assertCreated();

    $application = JobApplication::query()->firstOrFail();

    Notification::assertSentTo(
        $user,
        GeneralNotification::class,
        function (GeneralNotification $notification) use ($user, $application) {
            $payload = $notification->toArray($user);

            return str_contains((string) ($payload['message'] ?? ''), "Application No: {$application->uuid}");
        }
    );
});

test('job application payment submission sends user notification with application number', function () {
    Notification::fake();

    $smsService = mock(NotificationSmsSenderInterface::class);
    $smsService->shouldReceive('canSend')->with(1)->andReturn(true);
    $smsService->shouldReceive('sendTemplate')
        ->once()
        ->with(
            \Mockery::type('string'),
            'job-application-received',
            \Mockery::on(fn (array $variables): bool => isset($variables['name'], $variables['application_id'])),
            'transactional',
            \Mockery::type('int')
        )
        ->andReturn(SmsResponse::success());
    app()->instance(NotificationSmsSenderInterface::class, $smsService);

    $user = User::factory()->create(['mobile' => '+919876543210']);
    $recruitment = Recruitment::factory()->create([
        'status' => RecruitmentStatusCast::Published,
        'slug' => 'notif-paid-job',
        'is_payable' => true,
        'fees' => 45000,
    ]);

    $application = JobApplication::factory()->create([
        'applicant_type' => User::class,
        'applicant_id' => $user->id,
        'recruitment_id' => $recruitment->id,
        'status' => JobApplicationStatusCast::AwaitingPayment,
        'is_paid' => false,
    ]);

    $transaction = Transaction::create([
        'uuid' => 'TXN-JOB-NOTIF-' . fake()->randomNumber(6),
        'transactionable_type' => JobApplication::class,
        'transactionable_id' => $application->id,
        'type' => TransactionTypeCast::CREDIT,
        'status' => TransactionStatusCast::PENDING,
        'amount' => 45000,
        'purpose' => 'Job Application Fee',
        'payment_method' => 'cashfree',
    ]);

    $transaction->update([
        'status' => TransactionStatusCast::COMPLETED,
        'verified' => true,
        'verified_at' => now(),
    ]);

    event(new PaymentCompleted($transaction));

    Notification::assertSentTo(
        $user,
        GeneralNotification::class,
        function (GeneralNotification $notification) use ($user, $application) {
            $payload = $notification->toArray($user);

            return str_contains((string) ($payload['message'] ?? ''), "Application No: {$application->uuid}");
        }
    );
});

test('job application payment confirmation is idempotent for duplicate events', function () {
    Notification::fake();

    $smsService = mock(NotificationSmsSenderInterface::class);
    $smsService->shouldReceive('canSend')->with(1)->andReturn(true);
    $smsService->shouldReceive('sendTemplate')
        ->once()
        ->with(
            \Mockery::type('string'),
            'job-application-received',
            \Mockery::on(fn (array $variables): bool => isset($variables['name'], $variables['application_id'])),
            'transactional',
            \Mockery::type('int')
        )
        ->andReturn(SmsResponse::success());
    app()->instance(NotificationSmsSenderInterface::class, $smsService);

    $user = User::factory()->create(['mobile' => '+919876543210']);
    $recruitment = Recruitment::factory()->create([
        'status' => RecruitmentStatusCast::Published,
        'slug' => 'notif-paid-idempotent-job',
        'is_payable' => true,
        'fees' => 55000,
    ]);

    $application = JobApplication::factory()->create([
        'applicant_type' => User::class,
        'applicant_id' => $user->id,
        'recruitment_id' => $recruitment->id,
        'status' => JobApplicationStatusCast::AwaitingPayment,
        'is_paid' => false,
    ]);

    $transaction = Transaction::create([
        'uuid' => 'TXN-JOB-IDEMP-' . fake()->randomNumber(6),
        'transactionable_type' => JobApplication::class,
        'transactionable_id' => $application->id,
        'type' => TransactionTypeCast::CREDIT,
        'status' => TransactionStatusCast::PENDING,
        'amount' => 55000,
        'purpose' => 'Job Application Fee',
    ]);

    $transaction->update([
        'status' => TransactionStatusCast::COMPLETED,
        'verified' => true,
        'verified_at' => now(),
    ]);

    event(new PaymentCompleted($transaction));
    event(new PaymentCompleted($transaction->fresh()));

    Notification::assertSentToTimes($user, GeneralNotification::class, 1);
});
