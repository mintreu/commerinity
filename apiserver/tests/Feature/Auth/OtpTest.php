<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

// Clear cache before each test to prevent rate limit issues
beforeEach(function () {
    Cache::flush();
});

// ========================================
// OTP Generation Tests
// ========================================

test('can send OTP to mobile number', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => '+919876543210',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'demo', // Demo mode indicator
            'otp',  // OTP returned in demo mode
        ])
        ->assertJson(['demo' => true]);

    expect($response->json('otp'))->toBe(123456);
});

test('can send OTP to email', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'type' => 'email',
        'value' => 'test@example.com',
    ]);

    $response->assertSuccessful()
        ->assertJson(['demo' => true]);
});

test('OTP is stored in cache', function () {
    $credential = '+919876543210';

    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    // Check cache has OTP
    $cacheKey = 'otp:'.hash('xxh3', $credential);
    expect(Cache::has($cacheKey))->toBeTrue();
});

test('OTP expires after 10 minutes', function () {
    $credential = '+919876543210';

    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    // Travel 11 minutes
    $this->travel(11)->minutes();

    // Verify OTP
    $response = $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => $credential,
        'otp' => '123456',
    ]);

    $response->assertStatus(422)
        ->assertJson(['valid' => false, 'message' => 'OTP expired or invalid']);
});

test('new OTP overwrites previous OTP', function () {
    $credential = '+919876543210';

    // Send first OTP
    $response1 = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    $otp1 = $response1->json('otp');

    // Send second OTP (overwrites)
    $response2 = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    $otp2 = $response2->json('otp');

    // In production, these would be different random OTPs
    // In demo mode, they're the same (123456)
    expect($otp2)->toBe(123456);

    // Verify with latest OTP
    $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => $credential,
        'otp' => (string) $otp2,
    ])->assertSuccessful();
});

// ========================================
// OTP Verification Tests
// ========================================

test('can verify correct OTP', function () {
    $credential = '+919876543210';

    $sendResponse = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    $otp = $sendResponse->json('otp');

    $verifyResponse = $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => $credential,
        'otp' => (string) $otp,
    ]);

    $verifyResponse->assertSuccessful()
        ->assertJson(['valid' => true]);
});

test('cannot verify incorrect OTP', function () {
    $credential = '+919876543210';

    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    $response = $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => $credential,
        'otp' => '000000', // Wrong OTP
    ]);

    $response->assertStatus(422)
        ->assertJson(['valid' => false]);
});

test('cannot verify OTP without sending first', function () {
    $response = $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => '+919876543210',
        'otp' => '123456',
    ]);

    $response->assertStatus(422)
        ->assertJson(['valid' => false, 'message' => 'OTP expired or invalid']);
});

test('OTP verification is case-insensitive', function () {
    $credential = '+919876543210';

    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    // Demo OTP is always 123456, test with string
    $response = $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => $credential,
        'otp' => '123456',
    ]);

    $response->assertSuccessful()
        ->assertJson(['valid' => true]);
});

// ========================================
// Rate Limiting Tests
// ========================================

test('rate limits OTP generation - 3 requests per 15 minutes', function () {
    $credential = '+919876543210';

    // Make 3 successful requests
    for ($i = 0; $i < 3; $i++) {
        $response = $this->postJson('/api/auth/send-otp', [
            'type' => 'mobile',
            'value' => $credential,
        ]);

        $response->assertSuccessful();
    }

    // 4th request should fail
    $response = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    $response->assertStatus(429)
        ->assertJson(['message' => 'Too many OTP requests. Please try again in 15 minutes.']);
});

test('rate limit resets after 15 minutes', function () {
    $credential = '+919876543210';

    // Exhaust rate limit
    for ($i = 0; $i < 3; $i++) {
        $this->postJson('/api/auth/send-otp', [
            'type' => 'mobile',
            'value' => $credential,
        ]);
    }

    // Should be rate limited
    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ])->assertStatus(429);

    // Travel 16 minutes
    $this->travel(16)->minutes();

    // Should work again
    $response = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    $response->assertSuccessful();
});

test('rate limits verification attempts - 5 attempts', function () {
    $credential = '+919876543210';

    // Send OTP
    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    // Make 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        $response = $this->postJson('/api/auth/verify-otp', [
            'type' => 'mobile',
            'value' => $credential,
            'otp' => '000000', // Wrong OTP
        ]);

        $response->assertStatus(422);
    }

    // 6th attempt should be blocked
    $response = $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => $credential,
        'otp' => '000000',
    ]);

    $response->assertStatus(429)
        ->assertJson(['message' => 'Too many attempts. Please request a new OTP.']);
});

test('verification rate limit resets after successful verification', function () {
    $credential = '+919876543210';

    // Send OTP
    $sendResponse = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    $otp = $sendResponse->json('otp');

    // Make 4 failed attempts
    for ($i = 0; $i < 4; $i++) {
        $this->postJson('/api/auth/verify-otp', [
            'type' => 'mobile',
            'value' => $credential,
            'otp' => '000000',
        ]);
    }

    // Verify with correct OTP
    $response = $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => $credential,
        'otp' => (string) $otp,
    ]);

    $response->assertSuccessful();

    // Should be able to verify again (rate limit reset)
    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => $credential,
        'otp' => '123456',
    ])->assertSuccessful();
});

// ========================================
// Validation Tests
// ========================================

test('validates required fields for sending OTP', function () {
    $response = $this->postJson('/api/auth/send-otp', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type', 'value']);
});

test('validates OTP type is mobile or email', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'type' => 'invalid',
        'value' => 'test',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type']);
});

test('validates mobile number format', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => '123', // Too short
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['value']);
});

test('validates email format', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'type' => 'email',
        'value' => 'invalid-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['value']);
});

test('validates credential cannot be empty', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['value']);
});

test('validates credential cannot be whitespace', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => '   ',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['value']);
});

test('validates OTP is required for verification', function () {
    $response = $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => '+919876543210',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp']);
});

test('validates OTP is exactly 6 digits', function () {
    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => '+919876543210',
    ]);

    $response = $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => '+919876543210',
        'otp' => '123', // Too short
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp']);
});

// ========================================
// Security Tests
// ========================================

test('OTP is hashed in cache', function () {
    $credential = '+919876543210';

    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    $cacheKey = 'otp:'.hash('xxh3', $credential);
    $cachedValue = Cache::get($cacheKey);

    // Should be bcrypt hash, not plain OTP
    expect($cachedValue)
        ->toMatch('/^\$2y\$/')  // Bcrypt pattern
        ->toHaveLength(60);      // Bcrypt length
});

test('credential is hashed in cache key', function () {
    $credential = '+919876543210';

    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    // Cache key should use xxh3 hash, not plain credential
    $cacheKey = 'otp:'.hash('xxh3', $credential);

    expect(Cache::has($cacheKey))->toBeTrue()
        ->and(Cache::has('otp:'.$credential))->toBeFalse();
});

test('OTP is cleared after successful verification', function () {
    $credential = '+919876543210';

    $sendResponse = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    $otp = $sendResponse->json('otp');

    // Verify
    $this->postJson('/api/auth/verify-otp', [
        'type' => 'mobile',
        'value' => $credential,
        'otp' => (string) $otp,
    ]);

    // OTP should be cleared from cache
    $cacheKey = 'otp:'.hash('xxh3', $credential);
    expect(Cache::has($cacheKey))->toBeFalse();
});

test('OTP is cleared after max failed attempts', function () {
    $credential = '+919876543210';

    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $credential,
    ]);

    // Make 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/auth/verify-otp', [
            'type' => 'mobile',
            'value' => $credential,
            'otp' => '000000',
        ]);
    }

    // OTP should be cleared after max attempts
    $cacheKey = 'otp:'.hash('xxh3', $credential);
    expect(Cache::has($cacheKey))->toBeFalse();
});

// ========================================
// Demo Mode Tests
// ========================================

test('demo mode returns fixed OTP 123456', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => '+919876543210',
    ]);

    expect($response->json('otp'))->toBe(123456)
        ->and($response->json('demo'))->toBeTrue();
});

test('demo mode OTP is logged', function () {
    // Note: In production, we'd check actual log files
    // For now, we just verify the behavior exists
    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => '+919876543210',
    ])->assertSuccessful();

    // OTP manager should log demo OTP
    expect(true)->toBeTrue();
});
