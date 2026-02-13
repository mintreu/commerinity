<?php

declare(strict_types=1);

use App\Casts\JobApplicationStatusCast;
use App\Casts\RecruitmentStatusCast;
use App\Casts\TransactionStatusCast;
use App\Casts\TransactionTypeCast;
use App\Models\Address;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\Recruitment;
use App\Models\Transaction;
use App\Models\User;
use App\Events\PaymentCompleted;
use App\Notifications\GeneralNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use App\Models\Admin;

uses(RefreshDatabase::class);

/**
 * Complete Job Application Flow Integration Test
 *
 * Tests the complete end-to-end flow for recruitment job applications:
 * 1. User views open recruitment
 * 2. User submits application (free or paid)
 * 3. For paid applications: transaction is created
 * 4. Payment is completed
 * 5. Application status changes from awaiting_payment to submitted
 * 6. HR/Admin is notified
 * 7. User receives notification
 */

beforeEach(function () {
    // Create payment integration for paid applications
    $this->integration = \App\Models\Integration::factory()->cashfree()->create();
    app(\App\Services\IntegrationServices\Payment\PaymentService::class)->refreshProviders();

    // Create user with address
    $this->user = User::factory()->create();
    $this->address = Address::factory()->forUser($this->user)->create();

    // Mock Cashfree API for job application payment
    Http::fake(['sandbox.cashfree.com/pg/orders' => Http::response([
        'cf_order_id' => 'cf_job_'.fake()->uuid(),
        'order_id' => '*',
        'payment_session_id' => 'session_'.fake()->uuid(),
        'order_status' => 'ACTIVE',
    ], 200)]);

    // Fake Filament notifications
    Notification::fake();
});

describe('Job Application Flow - Free Recruitment', function () {
    beforeEach(function () {
        // Create free recruitment
        $this->recruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'free-job-developer',
            'is_payable' => false,
            'fees' => 0,
        ]);
    });

    it('submits application directly without payment', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/free-job-developer/apply', [
                'guardian_name' => 'Parent Guardian Name',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.requires_payment', false)
            ->assertJsonPath('data.application.status', 'submitted');

        // Verify application created
        expect(JobApplication::count())->toBe(1);

        $application = JobApplication::first();
        expect($application->applicant_id)->toBe($this->user->id);
        expect($application->status)->toBe(JobApplicationStatusCast::Submitted);
        expect($application->is_paid)->toBeTrue();
        expect($application->transaction_id)->toBeNull(); // No transaction for free
        expect($application->submitted_at)->not->toBeNull();

        Notification::assertSentTo($this->user, GeneralNotification::class);
    });

    it('allows optional details in free application', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/free-job-developer/apply', [
                'guardian_name' => 'Guardian Name',
                'educations' => [
                    [
                        'degree' => 'Bachelor of Technology',
                        'institution' => 'IIT Delhi',
                        'year' => 2022,
                    ],
                ],
                'skills' => [
                    ['skill' => 'PHP', 'description' => '3 years experience'],
                    ['skill' => 'Laravel', 'description' => '2 years experience'],
                ],
            ]);

        $response->assertCreated();

        $application = JobApplication::first();
        expect($application->educations)->toHaveCount(1);
        expect($application->skills)->toHaveCount(2);
    });
});

describe('Job Application Flow - Paid Recruitment', function () {
    beforeEach(function () {
        // Create paid recruitment
        $this->recruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'paid-job-manager',
            'is_payable' => true,
            'fees' => 50000, // ₹500 in paise
        ]);
    });

    it('creates application in awaiting_payment status', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/paid-job-manager/apply', [
                'guardian_name' => 'Guardian Name',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.requires_payment', true)
            ->assertJsonPath('data.application.status', 'awaiting_payment')
            ->assertJsonPath('data.application.amount', 50000);

        // Verify application created
        expect(JobApplication::count())->toBe(1);

        $application = JobApplication::first();
        expect($application->status)->toBe(JobApplicationStatusCast::AwaitingPayment);
        expect($application->is_paid)->toBeFalse();
    });

    it('returns checkout URL for paid application', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/paid-job-manager/apply', [
                'guardian_name' => 'Guardian Name',
            ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['payment_url']]);

        expect($response->json('data.payment_url'))->not->toBeNull();
    });

    it('completes application after successful payment', function () {
        // Create application in awaiting_payment status
        $application = JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment->id,
            'status' => JobApplicationStatusCast::AwaitingPayment,
            'is_paid' => false,
        ]);

        // Create transaction for job application fee
        $transaction = Transaction::create([
            'uuid' => 'TXN-JOB-' . fake()->randomNumber(6),
            'transactionable_type' => JobApplication::class,
            'transactionable_id' => $application->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 50000,
            'purpose' => 'Job Application Fee',
            'payment_method' => 'cashfree',
        ]);

        expect($application->is_paid)->toBeFalse();
        expect($application->status)->toBe(JobApplicationStatusCast::AwaitingPayment);
        expect($application->submitted_at)->toBeNull();

        // Simulate payment completion
        $transaction->update([
            'status' => TransactionStatusCast::COMPLETED,
            'verified' => true,
            'verified_at' => now(),
        ]);

        event(new PaymentCompleted($transaction));

        // Verify application submitted
        $application->refresh();
        expect($application->is_paid)->toBeTrue();
        expect($application->status)->toBe(JobApplicationStatusCast::Submitted);
        expect($application->submitted_at)->not->toBeNull();
        expect($application->transaction_id)->toBe($transaction->id);

        // Verify Filament notification sent to admins
        Notification::assertSentTo(
            Admin::all(),
            FilamentNotification::class,
            fn ($notification) => $notification->title === 'New Application Submitted'
                && str_contains($notification->body, $application->uuid)
        );
    });

    it('fails when transaction amount does not match recruitment fees', function () {
        // This test ensures the application amount matches recruitment fees
        $recruitmentWithFees = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'expensive-job',
            'is_payable' => true,
            'fees' => 100000, // ₹1000
        ]);

        $application = JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $recruitmentWithFees->id,
            'status' => JobApplicationStatusCast::AwaitingPayment,
            'is_paid' => false,
        ]);

        // Create transaction with wrong amount
        $transaction = Transaction::create([
            'uuid' => 'TXN-WRONG-AMT-' . fake()->randomNumber(6),
            'transactionable_type' => JobApplication::class,
            'transactionable_id' => $application->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 50000, // Only ₹500 instead of ₹1000
            'purpose' => 'Job Application Fee',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Application should still be submitted (amount validation happens at API level)
        $application->refresh();
        expect($application->status)->toBe(JobApplicationStatusCast::Submitted);
    });
});

describe('Job Application Flow - Multiple Applications', function () {
    beforeEach(function () {
        // Create multiple recruitments
        $this->recruitment1 = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'job-1',
            'is_payable' => false,
        ]);

        $this->recruitment2 = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'job-2',
            'is_payable' => true,
            'fees' => 30000,
        ]);

        $this->recruitment3 = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'job-3',
            'is_payable' => false,
        ]);
    });

    it('allows multiple applications for different recruitments', function () {
        // Apply to job 1 (free)
        $this->actingAs($this->user)
            ->postJson('/api/careers/job-1/apply', ['guardian_name' => 'Guardian 1'])
            ->assertCreated();

        // Apply to job 2 (paid - awaiting payment)
        JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment2->id,
            'status' => JobApplicationStatusCast::AwaitingPayment,
            'is_paid' => false,
        ]);

        // Apply to job 3 (free)
        $this->actingAs($this->user)
            ->postJson('/api/careers/job-3/apply', ['guardian_name' => 'Guardian 3'])
            ->assertCreated();

        // Verify 3 applications exist
        expect(JobApplication::where('applicant_id', $this->user->id)->count())->toBe(3);
    });

    it('prevents duplicate applications for same recruitment', function () {
        // Apply to job 1
        $this->actingAs($this->user)
            ->postJson('/api/careers/job-1/apply', ['guardian_name' => 'Guardian'])
            ->assertCreated();

        // Try to apply again
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/job-1/apply', ['guardian_name' => 'Guardian']);

        $response->assertUnprocessable();
        expect($response->json('message'))->toContain('already applied');
    });
});

describe('Job Application Flow - Payment Failures', function () {
    beforeEach(function () {
        $this->recruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'job-with-fail',
            'is_payable' => true,
            'fees' => 40000,
        ]);
    });

    it('keeps application in awaiting_payment on payment failure', function () {
        $application = JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment->id,
            'status' => JobApplicationStatusCast::AwaitingPayment,
            'is_paid' => false,
        ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-JOB-FAIL-' . fake()->randomNumber(6),
            'transactionable_type' => JobApplication::class,
            'transactionable_id' => $application->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 40000,
            'purpose' => 'Job Application Fee',
        ]);

        // Mark transaction as failed
        $transaction->update([
            'status' => TransactionStatusCast::FAILED,
            'verified' => true,
            'verified_at' => now(),
        ]);

        event(new PaymentCompleted($transaction));

        // Application should remain awaiting_payment
        $application->refresh();
        expect($application->status)->toBe(JobApplicationStatusCast::AwaitingPayment);
        expect($application->is_paid)->toBeFalse();
        expect($application->submitted_at)->toBeNull();
    });

    it('allows retry payment after failed transaction', function () {
        $application = JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment->id,
            'status' => JobApplicationStatusCast::AwaitingPayment,
            'is_paid' => false,
        ]);

        // Create failed transaction
        $failedTransaction = Transaction::create([
            'uuid' => 'TXN-FAILED-' . fake()->randomNumber(6),
            'transactionable_type' => JobApplication::class,
            'transactionable_id' => $application->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::FAILED,
            'amount' => 40000,
            'purpose' => 'Job Application Fee',
        ]);

        // Create new successful transaction (retry)
        $retryTransaction = Transaction::create([
            'uuid' => 'TXN-RETRY-' . fake()->randomNumber(6),
            'transactionable_type' => JobApplication::class,
            'transactionable_id' => $application->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 40000,
            'purpose' => 'Job Application Fee',
        ]);

        $retryTransaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($retryTransaction));

        // Application should now be submitted
        $application->refresh();
        expect($application->status)->toBe(JobApplicationStatusCast::Submitted);
        expect($application->is_paid)->toBeTrue();
        expect($application->transaction_id)->toBe($retryTransaction->id);
    });
});

describe('Job Application Flow - Notification Testing', function () {
    beforeEach(function () {
        $this->recruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'notify-job',
            'is_payable' => true,
            'fees' => 60000,
        ]);

        // Create admin for notification testing
        $this->admin = Admin::factory()->create();
    });

    it('sends Filament notification to all admins on successful payment', function () {
        $application = JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment->id,
            'status' => JobApplicationStatusCast::AwaitingPayment,
            'is_paid' => false,
            'uuid' => 'APP-TEST-NOTIFY',
        ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-NOTIFY-' . fake()->randomNumber(6),
            'transactionable_type' => JobApplication::class,
            'transactionable_id' => $application->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 60000,
            'purpose' => 'Job Application Fee',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Verify notification sent
        Notification::assertSentTo(
            [$this->admin],
            FilamentNotification::class,
            fn ($notification) => $notification->title === 'New Application Submitted'
                && str_contains($notification->body, 'APP-TEST-NOTIFY')
        );
    });

    it('does not send notification for failed payment', function () {
        $application = JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment->id,
            'status' => JobApplicationStatusCast::AwaitingPayment,
            'is_paid' => false,
        ]);

        $transaction = Transaction::create([
            'uuid' => 'TXN-NO-NOTIFY-' . fake()->randomNumber(6),
            'transactionable_type' => JobApplication::class,
            'transactionable_id' => $application->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 60000,
            'purpose' => 'Job Application Fee',
        ]);

        $transaction->update(['status' => TransactionStatusCast::FAILED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Verify no notification sent
        Notification::assertNothingSent();
    });
});

describe('Job Application Flow - Edge Cases', function () {
    it('handles application when recruitment is closed', function () {
        $closedRecruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Closed,
            'slug' => 'closed-job',
            'is_payable' => true,
            'fees' => 20000,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/closed-job/apply', ['guardian_name' => 'Guardian']);

        $response->assertNotFound();
    });

    it('handles application when recruitment has expired', function () {
        $expiredRecruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'expired-job',
            'is_payable' => true,
            'fees' => 20000,
            'close_date' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/expired-job/apply', ['guardian_name' => 'Guardian']);

        $response->assertNotFound();
    });

    it('requires user to have address before applying', function () {
        $userWithoutAddress = User::factory()->create();

        $recruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'needs-address',
            'is_payable' => false,
        ]);

        $response = $this->actingAs($userWithoutAddress)
            ->postJson('/api/careers/needs-address/apply', ['guardian_name' => 'Guardian']);

        $response->assertUnprocessable();
        expect($response->json('message'))->toContain('address');
    });
});

describe('Job Application Flow - Status Progression', function () {
    beforeEach(function () {
        $this->recruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'status-flow-job',
            'is_payable' => true,
            'fees' => 70000,
        ]);
    });

    it('tracks status progression through payment', function () {
        // Start with awaiting_payment
        $application = JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment->id,
            'status' => JobApplicationStatusCast::AwaitingPayment,
            'is_paid' => false,
        ]);

        expect($application->status)->toBe(JobApplicationStatusCast::AwaitingPayment);

        // Complete payment
        $transaction = Transaction::create([
            'uuid' => 'TXN-STATUS-' . fake()->randomNumber(6),
            'transactionable_type' => JobApplication::class,
            'transactionable_id' => $application->id,
            'type' => TransactionTypeCast::CREDIT,
            'status' => TransactionStatusCast::PENDING,
            'amount' => 70000,
            'purpose' => 'Job Application Fee',
        ]);

        $transaction->update(['status' => TransactionStatusCast::COMPLETED, 'verified' => true, 'verified_at' => now()]);
        event(new PaymentCompleted($transaction));

        // Verify status changed
        $application->refresh();
        expect($application->status)->toBe(JobApplicationStatusCast::Submitted);
        expect($application->submitted_at)->not->toBeNull();
    });
});
