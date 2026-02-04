<?php

declare(strict_types=1);

use App\Helpers\OtpManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    // Create user who signed up via mobile (simulating pre-onboarding state)
    // Let gender use default value from migration (OTHER)
    $this->userWithMobile = User::factory()->create([
        'mobile' => '+919876543210',
        'mobile_verified_at' => now(),
        'email' => null,
        'email_verified_at' => null,
        'onboarded' => false,
        'name' => 'Test User',
    ]);

    // Create user who signed up via email (needs to add mobile)
    $this->userWithEmail = User::factory()->create([
        'mobile' => null,
        'mobile_verified_at' => null,
        'email' => 'test@example.com',
        'email_verified_at' => now(),
        'onboarded' => false,
        'name' => 'Email User',
    ]);
});

test('can get onboarding status with mobile and email steps', function () {
    $response = $this->actingAs($this->userWithMobile)
        ->getJson('/api/onboarding/status');

    $response->assertOk()
        ->assertJsonStructure([
            'onboarded',
            'progress',
            'steps' => [
                'profile',
                'mobile',
                'email',
                'address',
                'kyc',
            ],
        ]);
});

test('onboarding status shows mobile as verified for mobile signup user', function () {
    $response = $this->actingAs($this->userWithMobile)
        ->getJson('/api/onboarding/status');

    $response->assertOk()
        ->assertJson([
            'steps' => [
                'mobile' => [
                    'complete' => true,
                ],
                'email' => [
                    'complete' => false,
                ],
            ],
        ]);
});

test('onboarding status shows mobile as not verified for email signup user', function () {
    $response = $this->actingAs($this->userWithEmail)
        ->getJson('/api/onboarding/status');

    $response->assertOk()
        ->assertJson([
            'steps' => [
                'mobile' => [
                    'complete' => false,
                ],
                'email' => [
                    'complete' => true,
                ],
            ],
        ]);
});

test('can update profile during onboarding', function () {
    $response = $this->actingAs($this->userWithMobile)
        ->putJson('/api/onboarding/profile', [
            'name' => 'Updated Name',
            'gender' => 'male',
            'dob' => '1990-01-15',
            'bio' => 'Test bio',
        ]);

    $response->assertOk()
        ->assertJson(['message' => 'Profile updated successfully']);

    $this->userWithMobile->refresh();
    expect($this->userWithMobile->name)->toBe('Updated Name');
    expect($this->userWithMobile->gender?->value)->toBe('male');
    expect($this->userWithMobile->dob->format('Y-m-d'))->toBe('1990-01-15');
});

test('can verify and add email during onboarding (optional)', function () {
    // Generate OTP
    $otpManager = new OtpManager(
        cache()->store(),
        app('hash'),
        true
    );
    $email = 'newemail@example.com';
    $otp = $otpManager->generate($email);

    $response = $this->actingAs($this->userWithMobile)
        ->postJson('/api/onboarding/verify-contact', [
            'type' => 'email',
            'value' => $email,
            'otp' => (string) $otp,
        ]);

    $response->assertOk()
        ->assertJson(['message' => 'Email verified and added successfully']);

    $this->userWithMobile->refresh();
    expect($this->userWithMobile->email)->toBe($email);
    expect($this->userWithMobile->email_verified_at)->not->toBeNull();
});

test('cannot verify email already used by another user', function () {
    User::factory()->create([
        'email' => 'taken@example.com',
        'email_verified_at' => now(),
    ]);

    $otpManager = new OtpManager(
        cache()->store(),
        app('hash'),
        true
    );
    $otp = $otpManager->generate('taken@example.com');

    $response = $this->actingAs($this->userWithMobile)
        ->postJson('/api/onboarding/verify-contact', [
            'type' => 'email',
            'value' => 'taken@example.com',
            'otp' => (string) $otp,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['value']);
});

test('can verify and add mobile during onboarding (required for email users)', function () {
    // Generate OTP
    $otpManager = new OtpManager(
        cache()->store(),
        app('hash'),
        true
    );
    $mobile = '+919999888877';
    $otp = $otpManager->generate($mobile);

    $response = $this->actingAs($this->userWithEmail)
        ->postJson('/api/onboarding/verify-contact', [
            'type' => 'mobile',
            'value' => $mobile,
            'otp' => (string) $otp,
        ]);

    $response->assertOk()
        ->assertJson(['message' => 'Mobile verified and added successfully']);

    $this->userWithEmail->refresh();
    expect($this->userWithEmail->mobile)->toBe($mobile);
    expect($this->userWithEmail->mobile_verified_at)->not->toBeNull();
});

test('cannot verify mobile already used by another user', function () {
    User::factory()->create([
        'mobile' => '+919999000001',
        'mobile_verified_at' => now(),
    ]);

    $otpManager = new OtpManager(
        cache()->store(),
        app('hash'),
        true
    );
    $otp = $otpManager->generate('+919999000001');

    $response = $this->actingAs($this->userWithEmail)
        ->postJson('/api/onboarding/verify-contact', [
            'type' => 'mobile',
            'value' => '+919999000001',
            'otp' => (string) $otp,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['value']);
});

test('cannot complete onboarding without mobile verification', function () {
    // Email user without mobile - should fail
    $this->userWithEmail->update([
        'name' => 'Email User',
        'dob' => '1990-01-15',
        'gender' => 'male',
    ]);

    $response = $this->actingAs($this->userWithEmail)
        ->postJson('/api/onboarding/complete');

    $response->assertStatus(422)
        ->assertJsonStructure(['message', 'missing'])
        ->assertJson([
            'missing' => ['mobile'],
        ]);
});

test('cannot complete onboarding without profile', function () {
    // Mobile user without profile completed - gender is default OTHER, but dob is null
    $response = $this->actingAs($this->userWithMobile)
        ->postJson('/api/onboarding/complete');

    $response->assertStatus(422)
        ->assertJsonStructure(['message', 'missing'])
        ->assertJson([
            'missing' => ['profile'],
        ]);
});

test('can complete onboarding with profile and mobile verified', function () {
    // Mobile user with profile complete - should succeed
    $this->userWithMobile->update([
        'name' => 'Test User',
        'dob' => '1990-01-15',
        'gender' => 'male',
    ]);

    $response = $this->actingAs($this->userWithMobile)
        ->postJson('/api/onboarding/complete');

    $response->assertOk()
        ->assertJson(['message' => 'Onboarding completed successfully! Welcome aboard!']);

    $this->userWithMobile->refresh();
    expect($this->userWithMobile->onboarded)->toBeTrue();
});

test('email user can complete onboarding after adding mobile', function () {
    // Generate OTP and add mobile first
    $otpManager = new OtpManager(
        cache()->store(),
        app('hash'),
        true
    );
    $mobile = '+919999888877';
    $otp = $otpManager->generate($mobile);

    // Add mobile
    $this->actingAs($this->userWithEmail)
        ->postJson('/api/onboarding/verify-contact', [
            'type' => 'mobile',
            'value' => $mobile,
            'otp' => (string) $otp,
        ]);

    // Complete profile
    $this->userWithEmail->update([
        'dob' => '1990-01-15',
        'gender' => 'female',
    ]);

    // Now should be able to complete onboarding
    $response = $this->actingAs($this->userWithEmail)
        ->postJson('/api/onboarding/complete');

    $response->assertOk()
        ->assertJson(['message' => 'Onboarding completed successfully! Welcome aboard!']);

    $this->userWithEmail->refresh();
    expect($this->userWithEmail->onboarded)->toBeTrue();
    expect($this->userWithEmail->mobile)->toBe($mobile);
    expect($this->userWithEmail->mobile_verified_at)->not->toBeNull();
});

test('cannot verify contact with invalid OTP', function () {
    $response = $this->actingAs($this->userWithMobile)
        ->postJson('/api/onboarding/verify-contact', [
            'type' => 'email',
            'value' => 'invalid-otp@example.com',
            'otp' => '000000',
        ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Invalid or expired OTP']);
});
