<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

// ========================================
// Email/Mobile Required Tests
// ========================================

test('login requires either email or mobile', function () {
    $response = $this->postJson('/api/auth/login', [
        'password' => 'Password123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'mobile']);
});

test('login accepts email only', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertSuccessful();
});

test('login accepts mobile only', function () {
    User::factory()->create([
        'mobile' => '+919876543210',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'mobile' => '+919876543210',
        'password' => 'Password123!',
    ]);

    $response->assertSuccessful();
});

// ========================================
// Email Validation Tests
// ========================================

test('login validates email format', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'invalid-email',
        'password' => 'Password123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('login accepts valid email formats', function ($email) {
    User::factory()->create([
        'email' => $email,
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $email,
        'password' => 'Password123!',
    ]);

    $response->assertSuccessful();
})->with([
    'simple' => 'user@example.com',
    'subdomain' => 'user@mail.example.com',
    'plus' => 'user+tag@example.com',
]);

// ========================================
// Mobile Validation Tests
// ========================================

test('login validates mobile format with E.164', function ($mobile) {
    $response = $this->postJson('/api/auth/login', [
        'mobile' => $mobile,
        'password' => 'Password123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['mobile']);
})->with([
    'too_short' => '123',
    'no_plus' => '919876543210',
    'letters' => '+91abcdefghij',
    'spaces' => '+91 98765 43210',
]);

test('login accepts valid E.164 mobile formats', function ($mobile) {
    User::factory()->create([
        'mobile' => $mobile,
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'mobile' => $mobile,
        'password' => 'Password123!',
    ]);

    $response->assertSuccessful();
})->with([
    'india' => '+919876543210',
    'us' => '+12025551234',
    'uk' => '+447911123456',
]);

// ========================================
// Password/OTP Required Tests
// ========================================

test('login requires either password or otp', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password', 'otp']);
});

test('login accepts password authentication', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertSuccessful();
});

// ========================================
// OTP Validation Tests
// ========================================

test('login validates otp must be 6 digits', function ($otp) {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'otp' => $otp,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['otp']);
})->with([
    'too_short' => '12345',
    'too_long' => '1234567',
    'letters' => 'abcdef',
    'mixed' => '123abc',
]);

test('login accepts valid 6-digit otp format', function () {
    // OTP format is valid, but actual verification is separate
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'otp' => '123456',
    ]);

    // Should not fail validation (may fail actual OTP check)
    $response->assertJsonMissingValidationErrors(['otp']);
});

// ========================================
// Device Name Validation Tests
// ========================================

test('login accepts optional device_name', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'device_name' => 'iPhone 15 Pro',
    ]);

    $response->assertSuccessful();
});

test('login device_name max length is 255', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'device_name' => str_repeat('a', 256),
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['device_name']);
});
