<?php

declare(strict_types=1);

use App\Casts\UserTypeCast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ========================================
// Registration with Mobile + OTP Tests
// ========================================

test('can register with mobile and OTP', function () {
    $mobile = '+919876543210';
    $otp = '123456'; // Demo mode OTP

    // Send OTP first
    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $mobile,
    ])->assertSuccessful();

    // Register with OTP
    $response = $this->postJson('/api/auth/register', [
        'mobile' => $mobile,
        'otp' => $otp,
        'name' => 'John Doe',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'uuid', 'name', 'mobile', 'type', 'status'],
                'token',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'mobile' => $mobile,
        'name' => 'John Doe',
    ]);

    expect(User::where('mobile', $mobile)->first())
        ->mobile_verified_at->not->toBeNull();
});

test('can register with mobile and optional email', function () {
    $mobile = '+919876543210';
    $email = 'test@example.com';
    $otp = '123456';

    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $mobile,
    ])->assertSuccessful();

    $response = $this->postJson('/api/auth/register', [
        'mobile' => $mobile,
        'email' => $email,
        'otp' => $otp,
        'name' => 'Jane Doe',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSuccessful();

    $user = User::where('mobile', $mobile)->first();
    expect($user)
        ->mobile->toBe($mobile)
        ->email->toBe($email)
        ->mobile_verified_at->not->toBeNull()
        ->type->toBe(UserTypeCast::REGULAR);
});

test('can register with referral code', function () {
    $referrer = User::factory()->create();
    $mobile = '+919876543210';

    // Send OTP first
    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $mobile,
    ])->assertSuccessful();

    $response = $this->postJson('/api/auth/register', [
        'mobile' => $mobile,
        'otp' => '123456',
        'name' => 'Referred User',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'referral_code' => $referrer->referral_code,
    ]);

    $response->assertSuccessful();

    $user = User::where('mobile', $mobile)->first();
    expect($user->parent_id)->toBe($referrer->id);
});

test('cannot register with invalid OTP', function () {
    $mobile = '+919876543210';

    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $mobile,
    ]);

    $response = $this->postJson('/api/auth/register', [
        'mobile' => $mobile,
        'otp' => '000000', // Wrong OTP
        'name' => 'John Doe',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp']);
});

test('cannot register with expired OTP', function () {
    $mobile = '+919876543210';

    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $mobile,
    ]);

    // Travel 11 minutes into future (OTP expires after 10 min)
    $this->travel(11)->minutes();

    $response = $this->postJson('/api/auth/register', [
        'mobile' => $mobile,
        'otp' => '123456',
        'name' => 'John Doe',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp']);
});

test('cannot register with existing mobile', function () {
    User::factory()->create(['mobile' => '+919876543210']);

    $response = $this->postJson('/api/auth/register', [
        'mobile' => '+919876543210',
        'otp' => '123456',
        'name' => 'Duplicate User',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['mobile']);
});

test('cannot register with existing email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson('/api/auth/register', [
        'email' => 'existing@example.com',
        'otp' => '123456',
        'name' => 'Duplicate User',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('cannot register with invalid referral code', function () {
    $response = $this->postJson('/api/auth/register', [
        'mobile' => '+919876543210',
        'otp' => '123456',
        'name' => 'Test User',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'referral_code' => 'INVALID99',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['referral_code']);
});

test('validates password strength', function () {
    $response = $this->postJson('/api/auth/register', [
        'mobile' => '+919876543210',
        'otp' => '123456',
        'name' => 'Test User',
        'password' => '123', // Too weak
        'password_confirmation' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('validates password confirmation', function () {
    $response = $this->postJson('/api/auth/register', [
        'mobile' => '+919876543210',
        'otp' => '123456',
        'name' => 'Test User',
        'password' => 'Password123!',
        'password_confirmation' => 'DifferentPassword!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('requires mobile', function () {
    $response = $this->postJson('/api/auth/register', [
        'email' => 'test@example.com',  // Email alone is not enough
        'otp' => '123456',
        'name' => 'Test User',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['mobile']);
});

test('registration creates Sanctum token', function () {
    $mobile = '+919876543210';

    // Send OTP first
    $this->postJson('/api/auth/send-otp', [
        'type' => 'mobile',
        'value' => $mobile,
    ])->assertSuccessful();

    $response = $this->postJson('/api/auth/register', [
        'mobile' => $mobile,
        'otp' => '123456',
        'name' => 'Token User',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['data' => ['token']]);

    $token = $response->json('data.token');
    expect($token)->not->toBeEmpty();

    // Verify token works
    $this->withToken($token)
        ->getJson('/api/user')
        ->assertSuccessful();
});

// ========================================
// Registration with Email + OTP Tests
// ========================================

test('can register with email and OTP', function () {
    $email = 'newuser@example.com';
    $otp = '123456'; // Demo mode OTP

    // Send OTP first
    $this->postJson('/api/auth/send-otp', [
        'type' => 'email',
        'value' => $email,
    ])->assertSuccessful();

    // Register with OTP
    $response = $this->postJson('/api/auth/register-email', [
        'email' => $email,
        'otp' => $otp,
        'name' => 'Email User',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'uuid', 'name', 'email', 'type', 'status'],
                'token',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => $email,
        'name' => 'Email User',
    ]);

    expect(User::where('email', $email)->first())
        ->email_verified_at->not->toBeNull();
});

test('can register with email and optional mobile', function () {
    $email = 'fullusertest@example.com';
    $mobile = '+919876543210';
    $otp = '123456';

    $this->postJson('/api/auth/send-otp', [
        'type' => 'email',
        'value' => $email,
    ])->assertSuccessful();

    $response = $this->postJson('/api/auth/register-email', [
        'email' => $email,
        'mobile' => $mobile,
        'otp' => $otp,
        'name' => 'Full User',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSuccessful();

    $user = User::where('email', $email)->first();
    expect($user)
        ->email->toBe($email)
        ->mobile->toBe($mobile)
        ->email_verified_at->not->toBeNull()
        ->type->toBe(UserTypeCast::REGULAR);
});

test('email registration can use referral code', function () {
    $referrer = User::factory()->create();
    $email = 'referred@example.com';

    // Send OTP first
    $this->postJson('/api/auth/send-otp', [
        'type' => 'email',
        'value' => $email,
    ])->assertSuccessful();

    $response = $this->postJson('/api/auth/register-email', [
        'email' => $email,
        'otp' => '123456',
        'name' => 'Referred Email User',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'referral_code' => $referrer->referral_code,
    ]);

    $response->assertSuccessful();

    $user = User::where('email', $email)->first();
    expect($user->parent_id)->toBe($referrer->id);
});

test('email registration cannot use invalid OTP', function () {
    $email = 'test@example.com';

    $this->postJson('/api/auth/send-otp', [
        'type' => 'email',
        'value' => $email,
    ]);

    $response = $this->postJson('/api/auth/register-email', [
        'email' => $email,
        'otp' => '000000', // Wrong OTP
        'name' => 'Test User',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['otp']);
});

test('email registration validates required fields', function () {
    $response = $this->postJson('/api/auth/register-email', [
        // Missing all required fields
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'otp', 'name', 'password']);
});

test('email registration creates Sanctum token', function () {
    $email = 'tokentest@example.com';

    // Send OTP first
    $this->postJson('/api/auth/send-otp', [
        'type' => 'email',
        'value' => $email,
    ])->assertSuccessful();

    $response = $this->postJson('/api/auth/register-email', [
        'email' => $email,
        'otp' => '123456',
        'name' => 'Token User',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['data' => ['token']]);

    $token = $response->json('data.token');
    expect($token)->not->toBeEmpty();

    // Verify token works
    $this->withToken($token)
        ->getJson('/api/user')
        ->assertSuccessful();
});
