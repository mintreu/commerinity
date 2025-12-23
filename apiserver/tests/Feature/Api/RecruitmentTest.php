<?php

declare(strict_types=1);

use App\Casts\JobApplicationStatusCast;
use App\Casts\RecruitmentRoleCast;
use App\Casts\RecruitmentStatusCast;
use App\Casts\RecruitmentTypeCast;
use App\Models\Address;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\Recruitment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ========================================
// RECRUITMENT LISTING TESTS (PUBLIC)
// ========================================

describe('Public Recruitment Listing', function () {
    it('returns empty list when no recruitments exist', function () {
        $response = $this->getJson('/api/careers');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('returns only published and open recruitments', function () {
        // Create different status recruitments
        Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Draft,
            'title' => 'Draft Job',
        ]);
        Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Closed,
            'title' => 'Closed Job',
        ]);
        Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'title' => 'Open Job',
            'open_date' => now()->subDay(),
            'close_date' => now()->addWeek(),
        ]);

        $response = $this->getJson('/api/careers');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Open Job']);
    });

    it('excludes recruitments with future open dates', function () {
        Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'title' => 'Future Job',
            'open_date' => now()->addWeek(),
            'close_date' => now()->addMonth(),
        ]);

        $response = $this->getJson('/api/careers');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('excludes recruitments with past close dates', function () {
        Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'title' => 'Expired Job',
            'open_date' => now()->subMonth(),
            'close_date' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/careers');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('can filter by role', function () {
        Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'role' => RecruitmentRoleCast::Advisor,
            'title' => 'Advisor Position',
        ]);
        Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'role' => RecruitmentRoleCast::Trainer,
            'title' => 'Trainer Position',
        ]);

        $response = $this->getJson('/api/careers?role=advisor');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Advisor Position']);
    });

    it('can filter by employment type', function () {
        Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'employment_type' => RecruitmentTypeCast::FullTime,
            'title' => 'Full Time Job',
        ]);
        Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'employment_type' => RecruitmentTypeCast::PartTime,
            'title' => 'Part Time Job',
        ]);

        $response = $this->getJson('/api/careers?type=full_time');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Full Time Job']);
    });

    it('paginates results', function () {
        Recruitment::factory()->count(15)->create([
            'status' => RecruitmentStatusCast::Published,
        ]);

        $response = $this->getJson('/api/careers?per_page=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 15);
    });
});

// ========================================
// RECRUITMENT DETAIL TESTS (PUBLIC)
// ========================================

describe('Public Recruitment Detail', function () {
    it('returns recruitment by slug', function () {
        $recruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'title' => 'Senior Developer',
            'slug' => 'senior-developer-abc123',
        ]);

        $response = $this->getJson('/api/careers/senior-developer-abc123');

        $response->assertOk()
            ->assertJsonPath('data.title', 'Senior Developer')
            ->assertJsonPath('data.slug', 'senior-developer-abc123');
    });

    it('returns 404 for non-existent slug', function () {
        $response = $this->getJson('/api/careers/non-existent-job');

        $response->assertNotFound();
    });

    it('includes formatted fees for payable recruitment', function () {
        Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'paid-job',
            'is_payable' => true,
            'fees' => 50000, // 500 rupees in paisa
        ]);

        $response = $this->getJson('/api/careers/paid-job');

        $response->assertOk()
            ->assertJsonPath('data.is_payable', true)
            ->assertJsonPath('data.fees', 50000)
            ->assertJsonPath('data.fees_in_rupees', 500);
    });

    it('returns filter options', function () {
        $response = $this->getJson('/api/careers/filters');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'roles',
                    'types',
                    'counts_by_role',
                ],
            ]);
    });
});

// ========================================
// JOB APPLICATION TESTS (AUTHENTICATED)
// ========================================

describe('Job Application - Authentication', function () {
    it('requires authentication to apply', function () {
        $recruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'test-job',
        ]);

        $response = $this->postJson('/api/careers/test-job/apply', [
            'guardian_name' => 'Parent Name',
        ]);

        $response->assertUnauthorized();
    });

    it('requires authentication to view my applications', function () {
        $response = $this->getJson('/api/my-applications');

        $response->assertUnauthorized();
    });

    it('requires authentication to check application status', function () {
        $recruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'test-job',
        ]);

        $response = $this->getJson('/api/careers/test-job/check-application');

        $response->assertUnauthorized();
    });
});

describe('Job Application - Free Recruitment', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->address = Address::factory()
            ->forUser($this->user)
            ->default()
            ->create();
        $this->recruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'free-job',
            'is_payable' => false,
        ]);
    });

    it('can apply to free recruitment', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/free-job/apply', [
                'guardian_name' => 'Parent Name',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.requires_payment', false)
            ->assertJsonPath('data.application.status', 'submitted');

        $this->assertDatabaseHas('job_applications', [
            'recruitment_id' => $this->recruitment->id,
            'applicant_id' => $this->user->id,
            'status' => 'submitted',
        ]);
    });

    it('validates guardian name is required', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/free-job/apply', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['guardian_name']);
    });

    it('prevents duplicate applications', function () {
        // First application
        $this->actingAs($this->user)
            ->postJson('/api/careers/free-job/apply', [
                'guardian_name' => 'Parent Name',
            ]);

        // Second application attempt
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/free-job/apply', [
                'guardian_name' => 'Parent Name',
            ]);

        $response->assertUnprocessable();
        expect($response->json('message'))->toContain('already applied');
    });

    it('requires user to have an address', function () {
        $userWithoutAddress = User::factory()->create();

        $response = $this->actingAs($userWithoutAddress)
            ->postJson('/api/careers/free-job/apply', [
                'guardian_name' => 'Parent Name',
            ]);

        $response->assertUnprocessable();
        expect($response->json('message'))->toContain('address');
    });

    it('can include optional education details', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/free-job/apply', [
                'guardian_name' => 'Parent Name',
                'educations' => [
                    [
                        'degree' => 'Bachelor of Science',
                        'institution' => 'Delhi University',
                        'year' => 2020,
                    ],
                ],
            ]);

        $response->assertCreated();

        $application = JobApplication::where('applicant_id', $this->user->id)->first();
        expect($application->educations)->toHaveCount(1);
        expect($application->educations[0]['degree'])->toBe('Bachelor of Science');
    });

    it('can include optional skills', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/free-job/apply', [
                'guardian_name' => 'Parent Name',
                'skills' => [
                    ['skill' => 'PHP', 'description' => '5 years experience'],
                    ['skill' => 'Laravel', 'description' => '3 years experience'],
                ],
            ]);

        $response->assertCreated();

        $application = JobApplication::where('applicant_id', $this->user->id)->first();
        expect($application->skills)->toHaveCount(2);
    });
});

describe('Job Application - Paid Recruitment', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->address = Address::factory()
            ->forUser($this->user)
            ->default()
            ->create();
        $this->recruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'paid-job',
            'is_payable' => true,
            'fees' => 50000, // 500 rupees
        ]);
    });

    it('creates application with awaiting_payment status for paid recruitment', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/paid-job/apply', [
                'guardian_name' => 'Parent Name',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.requires_payment', true)
            ->assertJsonPath('data.application.status', 'awaiting_payment')
            ->assertJsonPath('data.application.amount', 50000);
    });

    it('returns payment URL for paid applications', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/paid-job/apply', [
                'guardian_name' => 'Parent Name',
            ]);

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['payment_url']]);

        expect($response->json('data.payment_url'))->not->toBeNull();
    });
});

describe('Job Application - Closed Recruitment', function () {
    it('cannot apply to closed recruitment', function () {
        $user = User::factory()->create();
        Address::factory()
            ->forUser($user)
            ->default()
            ->create();

        Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Closed,
            'slug' => 'closed-job',
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/careers/closed-job/apply', [
                'guardian_name' => 'Parent Name',
            ]);

        $response->assertNotFound();
    });

    it('cannot apply to expired recruitment', function () {
        $user = User::factory()->create();
        Address::factory()
            ->forUser($user)
            ->default()
            ->create();

        Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'expired-job',
            'close_date' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/careers/expired-job/apply', [
                'guardian_name' => 'Parent Name',
            ]);

        $response->assertNotFound();
    });
});

// ========================================
// MY APPLICATIONS TESTS
// ========================================

describe('My Applications', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('returns empty list when user has no applications', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/my-applications');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('returns user applications with recruitment details', function () {
        $recruitment = Recruitment::factory()->create([
            'title' => 'Test Job',
        ]);

        JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $recruitment->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/my-applications');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.recruitment.title', 'Test Job');
    });

    it('does not return other users applications', function () {
        $otherUser = User::factory()->create();
        $recruitment = Recruitment::factory()->create();

        JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $otherUser->id,
            'recruitment_id' => $recruitment->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/my-applications');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('can view specific application by UUID', function () {
        $recruitment = Recruitment::factory()->create();
        $application = JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $recruitment->id,
            'uuid' => 'APP-2412-TESTTEST',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/my-applications/APP-2412-TESTTEST');

        $response->assertOk()
            ->assertJsonPath('data.uuid', 'APP-2412-TESTTEST');
    });

    it('returns 404 for non-existent application', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/my-applications/APP-0000-NOTFOUND');

        $response->assertNotFound();
    });

    it('cannot view other users application', function () {
        $otherUser = User::factory()->create();
        $recruitment = Recruitment::factory()->create();

        JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $otherUser->id,
            'recruitment_id' => $recruitment->id,
            'uuid' => 'APP-2412-OTHERUSER',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/my-applications/APP-2412-OTHERUSER');

        $response->assertNotFound();
    });
});

// ========================================
// APPLICATION WITHDRAWAL TESTS
// ========================================

describe('Application Withdrawal', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->recruitment = Recruitment::factory()->create();
    });

    it('can withdraw submitted application', function () {
        $application = JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment->id,
            'uuid' => 'APP-2412-WITHDRAW1',
            'status' => JobApplicationStatusCast::Submitted,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/my-applications/APP-2412-WITHDRAW1/withdraw', [
                'reason' => 'Changed my mind',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'withdrawn');

        $this->assertDatabaseHas('job_applications', [
            'uuid' => 'APP-2412-WITHDRAW1',
            'status' => 'withdrawn',
            'status_feedback' => 'Changed my mind',
        ]);
    });

    it('can withdraw application under review', function () {
        $application = JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment->id,
            'uuid' => 'APP-2412-WITHDRAW2',
            'status' => JobApplicationStatusCast::UnderReview,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/my-applications/APP-2412-WITHDRAW2/withdraw');

        $response->assertOk()
            ->assertJsonPath('data.status', 'withdrawn');
    });

    it('cannot withdraw draft application', function () {
        JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment->id,
            'uuid' => 'APP-2412-DRAFT',
            'status' => JobApplicationStatusCast::Draft,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/my-applications/APP-2412-DRAFT/withdraw');

        $response->assertUnprocessable();
    });

    it('cannot withdraw accepted application', function () {
        JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment->id,
            'uuid' => 'APP-2412-ACCEPTED',
            'status' => JobApplicationStatusCast::Accepted,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/my-applications/APP-2412-ACCEPTED/withdraw');

        $response->assertUnprocessable();
    });

    it('cannot withdraw rejected application', function () {
        JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment->id,
            'uuid' => 'APP-2412-REJECTED',
            'status' => JobApplicationStatusCast::Rejected,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/my-applications/APP-2412-REJECTED/withdraw');

        $response->assertUnprocessable();
    });
});

// ========================================
// CHECK APPLICATION STATUS TESTS
// ========================================

describe('Check Application Status', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->recruitment = Recruitment::factory()->create([
            'status' => RecruitmentStatusCast::Published,
            'slug' => 'check-job',
        ]);
    });

    it('returns has_applied false when user has not applied', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/careers/check-job/check-application');

        $response->assertOk()
            ->assertJsonPath('data.has_applied', false)
            ->assertJsonPath('data.application', null);
    });

    it('returns has_applied true with application when user has applied', function () {
        JobApplication::factory()->create([
            'applicant_type' => User::class,
            'applicant_id' => $this->user->id,
            'recruitment_id' => $this->recruitment->id,
            'uuid' => 'APP-2412-CHECKAPP',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/careers/check-job/check-application');

        $response->assertOk()
            ->assertJsonPath('data.has_applied', true)
            ->assertJsonPath('data.application.uuid', 'APP-2412-CHECKAPP');
    });
});
