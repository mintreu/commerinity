<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['services.sms.options.demo_mode' => true]);
    Cache::flush();
});

// ========================================
// Forgot Password Tests
// ========================================

test('can request password reset link via email', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertSuccessful()
        ->assertJson(['message' => 'Password reset link sent to your email']);

    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => 'test@example.com',
    ]);
});

test('can request password reset via mobile OTP', function () {
    $user = User::factory()->create(['mobile' => '9876543210']);

    $response = $this->postJson('/api/auth/forgot-password-mobile', [
        'mobile' => '9876543210',
    ]);

    $response->assertSuccessful()
        ->assertJson(['message' => 'OTP sent successfully']);
});

test('cannot request password reset for non-existent email', function () {
    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(404)
        ->assertJson(['message' => 'Email not found']);
});

test('cannot request password reset for non-existent mobile', function () {
    $response = $this->postJson('/api/auth/forgot-password-mobile', [
        'mobile' => '9999999999',
    ]);

    $response->assertStatus(404)
        ->assertJson(['message' => 'Mobile number not found']);
});

// ========================================
// Reset Password with Token (Email) Tests
// ========================================

test('can reset password with valid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('OldPassword123!'),
    ]);

    // Request reset
    $resetResponse = $this->postJson('/api/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);

    // Get token from response (plain token, not hashed)
    $token = $resetResponse->json('token');

    // Reset password
    $response = $this->postJson('/api/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertSuccessful()
        ->assertJson(['message' => 'Password reset successfully']);

    // Verify new password works
    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'NewPassword123!',
    ])->assertSuccessful();

    // Verify old password doesn't work
    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'OldPassword123!',
    ])->assertStatus(401);
});

test('cannot reset password with invalid token', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/api/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => 'invalid-token',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Invalid or expired reset token']);
});

test('cannot reset password with expired token', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    // Create expired token
    \DB::table('password_reset_tokens')->insert([
        'email' => 'test@example.com',
        'token' => Hash::make('expired-token'),
        'created_at' => now()->subHours(2), // Expired (valid for 1 hour)
    ]);

    $response = $this->postJson('/api/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => 'expired-token',
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Invalid or expired reset token']);
});

test('token is deleted after successful password reset', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    // Get plain token from API response
    $resetResponse = $this->postJson('/api/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);
    $token = $resetResponse->json('token');

    $this->postJson('/api/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $this->assertDatabaseMissing('password_reset_tokens', [
        'email' => 'test@example.com',
    ]);
});

// ========================================
// Reset Password with Mobile + OTP Tests
// ========================================

test('can reset password with mobile and OTP', function () {
    $user = User::factory()->create([
        'mobile' => '9876543210',
        'password' => Hash::make('OldPassword123!'),
    ]);

    // Request OTP
    $this->postJson('/api/auth/forgot-password-mobile', [
        'mobile' => '9876543210',
    ]);

    // Reset with OTP
    $response = $this->postJson('/api/auth/reset-password-mobile', [
        'mobile' => '9876543210',
        'otp' => '123456', // Demo mode
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertSuccessful();

    // Verify new password works
    $this->postJson('/api/auth/login', [
        'mobile' => '9876543210',
        'password' => 'NewPassword123!',
    ])->assertSuccessful();
});

test('cannot reset password with mobile and invalid OTP', function () {
    $user = User::factory()->create(['mobile' => '9876543210']);

    $this->postJson('/api/auth/forgot-password-mobile', [
        'mobile' => '9876543210',
    ]);

    $response = $this->postJson('/api/auth/reset-password-mobile', [
        'mobile' => '9876543210',
        'otp' => '000000', // Wrong OTP
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp']);
});

// ========================================
// Password Validation Tests
// ========================================

test('validates password strength on reset', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $this->postJson('/api/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $token = \DB::table('password_reset_tokens')
        ->where('email', 'test@example.com')
        ->first()->token;

    $response = $this->postJson('/api/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => '123', // Too weak
        'password_confirmation' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('validates password confirmation on reset', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $this->postJson('/api/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $token = \DB::table('password_reset_tokens')
        ->where('email', 'test@example.com')
        ->first()->token;

    $response = $this->postJson('/api/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'DifferentPassword!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

// ========================================
// Rate Limiting Tests
// ========================================

test('rate limits password reset requests', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    // Make 6 requests (limit is typically 5 per hour)
    for ($i = 0; $i < 6; $i++) {
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        if ($i < 5) {
            $response->assertSuccessful();
        }
    }

    // 6th request should be rate limited
    $response->assertStatus(429)
        ->assertJson(['message' => 'Too many reset attempts. Please try again later.']);
});

// ========================================
// Security Tests
// ========================================

test('password reset tokens are hashed in database', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    $this->postJson('/api/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $dbToken = \DB::table('password_reset_tokens')
        ->where('email', 'test@example.com')
        ->first()->token;

    // Token in database should be hashed, not plain text
    expect($dbToken)
        ->toMatch('/^\$2y\$/')  // Bcrypt hash pattern
        ->toHaveLength(60);      // Bcrypt hash length
});

test('cannot use same reset token twice', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    // Get plain token from API response
    $resetResponse = $this->postJson('/api/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);
    $token = $resetResponse->json('token');

    // First reset
    $this->postJson('/api/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ])->assertSuccessful();

    // Try using same token again
    $response = $this->postJson('/api/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => $token,
        'password' => 'AnotherPassword123!',
        'password_confirmation' => 'AnotherPassword123!',
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Invalid or expired reset token']);
});

test('all user tokens are revoked after password reset', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('OldPassword123!'),
    ]);

    // Login and get token (response is { "token": "..." })
    $oldToken = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'OldPassword123!',
    ])->json('token');

    // Request and perform password reset - get plain token from API response
    $resetResponse = $this->postJson('/api/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);
    $resetToken = $resetResponse->json('token');

    $this->postJson('/api/auth/reset-password', [
        'email' => 'test@example.com',
        'token' => $resetToken,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    // Refresh to clear any cached auth
    $this->refreshApplication();

    // Old token should no longer work
    $this->withToken($oldToken)
        ->getJson('/api/user')
        ->assertStatus(401);
});
