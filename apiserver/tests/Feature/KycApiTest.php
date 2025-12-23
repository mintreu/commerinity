<?php

declare(strict_types=1);

use App\Models\Kyc;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user without kyc sees no kyc found', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/kyc/status');
    $response->assertSuccessful()->assertJsonPath('data.has_kyc', false);
});

test('pan number format validation', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson('/api/kyc/submit', [
        'kyc_type' => 'personal',
        'pan_number' => 'INVALID123',
    ]);
    $response->assertUnprocessable()->assertJsonValidationErrors('pan_number');
});

test('cannot submit duplicate kyc when pending', function () {
    $user = User::factory()->create();
    Kyc::factory()->for($user, 'kycable')->pending()->create(['pan_number' => 'AAAAA1111A']);

    // Service layer correctly identifies pending KYC
    $service = app(\App\Services\KycService::class);
    $result = $service->canSubmitKyc($user->fresh());
    expect($result['can_submit'])->toBeFalse();
    expect($result['reason'])->toBe('You already have a pending KYC submission');
});

test('cannot submit kyc when already approved', function () {
    $user = User::factory()->create();
    Kyc::factory()->for($user, 'kycable')->approved()->create(['pan_number' => 'CCCCC3333C']);

    // Service layer correctly identifies approved KYC
    $service = app(\App\Services\KycService::class);
    $result = $service->canSubmitKyc($user->fresh());
    expect($result['can_submit'])->toBeFalse();
    expect($result['reason'])->toBe('You already have an approved KYC');
});

test('user helper method checks approved kyc', function () {
    $user = User::factory()->create();
    expect($user->hasApprovedKyc())->toBeFalse();

    Kyc::factory()->for($user, 'kycable')->approved()->create();

    expect($user->fresh()->hasApprovedKyc())->toBeTrue();
});

test('user hasOne kyc returns latest record', function () {
    $user = User::factory()->create();
    Kyc::factory()->for($user, 'kycable')->rejected()->create();
    $latest = Kyc::factory()->for($user, 'kycable')->pending()->create();

    expect($user->kyc->id)->toBe($latest->id);
});

test('unauthenticated user cannot access kyc', function () {
    $response = $this->getJson('/api/kyc/status');
    $response->assertUnauthorized();
});

test('aadhaar number is masked in response', function () {
    $user = User::factory()->create();
    Kyc::factory()->for($user, 'kycable')->create(['aadhaar_number' => '123456789012']);

    $response = $this->actingAs($user)->getJson('/api/kyc/status');

    $response->assertSuccessful()->assertJsonPath('data.kyc.aadhaar_number', '********9012');
});

test('business kyc includes company details', function () {
    $user = User::factory()->create();
    Kyc::factory()->for($user, 'kycable')->business()->create();

    $response = $this->actingAs($user)->getJson('/api/kyc/status');

    $response->assertSuccessful()
        ->assertJsonPath('data.kyc.kyc_type', 'business')
        ->assertJsonStructure(['data' => ['kyc' => ['company_name', 'gst_number']]]);
});

test('kyc model scopes work correctly', function () {
    Kyc::factory()->pending()->create();
    Kyc::factory()->approved()->create();
    Kyc::factory()->rejected()->create();

    expect(Kyc::pending()->count())->toBe(1);
    expect(Kyc::approved()->count())->toBe(1);
    expect(Kyc::rejected()->count())->toBe(1);
});

test('kyc status returns correct enum', function () {
    $user = User::factory()->create();
    Kyc::factory()->for($user, 'kycable')->approved()->create();

    $response = $this->actingAs($user)->getJson('/api/kyc/status');

    $response->assertSuccessful()->assertJsonPath('data.kyc.status', 'approved');
});

test('kyc service canSubmitKyc works correctly', function () {
    $service = app(\App\Services\KycService::class);
    $user = User::factory()->create();

    $result = $service->canSubmitKyc($user);
    expect($result['can_submit'])->toBeTrue();

    Kyc::factory()->for($user, 'kycable')->pending()->create();
    $result = $service->canSubmitKyc($user->fresh());
    expect($result['can_submit'])->toBeFalse();
    expect($result['reason'])->toContain('pending');
});

test('kyc service handles rejected kyc resubmission', function () {
    $service = app(\App\Services\KycService::class);
    $user = User::factory()->create();

    Kyc::factory()->for($user, 'kycable')->rejected()->create();

    $result = $service->canSubmitKyc($user->fresh());
    expect($result['can_submit'])->toBeTrue();
});
