<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

// ========================================
// Login with Email/Password Tests
// ========================================

test('can login with email and password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['token']);
});

test('can login with mobile and password', function () {
    $user = User::factory()->create([
        'mobile' => '9876543210',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'mobile' => '9876543210',
        'password' => 'Password123!',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['token']);
});

test('cannot login with incorrect password', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'WrongPassword!',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Invalid credentials']);
});

test('cannot login with non-existent email', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(401);
});

test('cannot login with banned user', function () {
    $user = User::factory()->create([
        'email' => 'banned@example.com',
        'password' => Hash::make('Password123!'),
        'status' => \App\Casts\UserStatusCast::BANNED->value,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'banned@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(403)
        ->assertJson(['message' => 'Account is banned']);
});

test('cannot login with suspended user', function () {
    $user = User::factory()->create([
        'email' => 'suspended@example.com',
        'password' => Hash::make('Password123!'),
        'status' => \App\Casts\UserStatusCast::SUSPENDED->value,
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'suspended@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(403)
        ->assertJson(['message' => 'Account is suspended']);
});

// ========================================
// Login with Mobile + OTP (Passwordless) Tests
// ========================================
// TODO: Implement OTP login endpoints

// ========================================
// Token Management Tests
// ========================================

test('login creates new Sanctum token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $token = $response->json('token');

    expect($token)->not->toBeEmpty();
    expect($user->fresh()->tokens()->count())->toBe(1);
});

test('can have multiple active sessions', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    // Login from device 1
    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'device_name' => 'iPhone',
    ]);

    // Login from device 2
    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'device_name' => 'iPad',
    ]);

    expect($user->fresh()->tokens()->count())->toBe(2);
});

test('login token can access protected routes', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $loginResponse = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $token = $loginResponse->json('token');

    $response = $this->withToken($token)
        ->getJson('/api/user');

    $response->assertSuccessful()
        ->assertJsonStructure(['data' => ['uuid', 'name', 'email', 'type', 'status']]);
});

// ========================================
// Logout Tests
// ========================================

test('can logout and revoke current token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $loginResponse = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $token = $loginResponse->json('token');

    // Logout
    $logoutResponse = $this->withToken($token)
        ->postJson('/api/auth/logout');

    $logoutResponse->assertSuccessful();

    // Verify token was actually deleted from database
    $tokenId = explode('|', $token)[0];
    expect(\Laravel\Sanctum\PersonalAccessToken::find($tokenId))->toBeNull();

    // Refresh application to clear any cached authentication
    $this->refreshApplication();

    // Token should no longer work
    $this->withToken($token)
        ->getJson('/api/user')
        ->assertStatus(401);
});

test('logout only revokes current device token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    // Login from device 1
    $device1Response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'device_name' => 'iPhone',
    ]);

    // Login from device 2
    $device2Response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'device_name' => 'iPad',
    ]);

    $token1 = $device1Response->json('token');
    $token2 = $device2Response->json('token');

    // Verify both tokens exist before logout
    expect($user->fresh()->tokens()->count())->toBe(2);

    // Logout from device 1
    $this->withToken($token1)
        ->postJson('/api/auth/logout')
        ->assertSuccessful();

    // Verify token 1 was deleted from database
    $token1Id = explode('|', $token1)[0];
    expect(\Laravel\Sanctum\PersonalAccessToken::find($token1Id))->toBeNull();

    // Verify token 2 still exists in database
    $token2Id = explode('|', $token2)[0];
    expect(\Laravel\Sanctum\PersonalAccessToken::find($token2Id))->not->toBeNull();

    // Verify user now has only 1 token
    expect($user->fresh()->tokens()->count())->toBe(1);
});

test('can logout from all devices', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    // Login from 2 devices
    $device1 = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ])->json('token');

    $device2 = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ])->json('token');

    // Logout from all devices
    $this->withToken($device1)
        ->postJson('/api/auth/logout-all');

    // Refresh application to clear cached authentication
    $this->refreshApplication();

    // Both tokens should not work
    $this->withToken($device1)->getJson('/api/user')->assertStatus(401);
    $this->withToken($device2)->getJson('/api/user')->assertStatus(401);
});

// ========================================
// Validation Tests
// ========================================

test('login requires email or mobile', function () {
    $response = $this->postJson('/api/auth/login', [
        'password' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'mobile']);
});

test('login requires password', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('validates email format', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'invalid-email',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('validates mobile format', function () {
    $response = $this->postJson('/api/auth/login', [
        'mobile' => '123', // Too short
        'password' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['mobile']);
});

// ========================================
// Mobile Login Endpoint Tests (/api/auth/login-mobile)
// ========================================

test('loginMobile requires device_type parameter', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login-mobile', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'device_type is required (android or ios)']);
});

test('loginMobile with android device_type works', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login-mobile', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'device_type' => 'android',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['token']);

    // Verify token name is android
    expect($user->fresh()->tokens()->first()->name)->toBe('android');
});

test('loginMobile with ios device_type works', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login-mobile', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'device_type' => 'ios',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['token']);

    // Verify token name is ios
    expect($user->fresh()->tokens()->first()->name)->toBe('ios');
});

test('loginMobile returns token with correct device name', function () {
    $user = User::factory()->create([
        'mobile' => '9876543210',
        'password' => Hash::make('Password123!'),
    ]);

    $this->postJson('/api/auth/login-mobile', [
        'mobile' => '9876543210',
        'password' => 'Password123!',
        'device_type' => 'android',
    ]);

    $this->postJson('/api/auth/login-mobile', [
        'mobile' => '9876543210',
        'password' => 'Password123!',
        'device_type' => 'ios',
    ]);

    $tokenNames = $user->fresh()->tokens()->pluck('name')->toArray();
    expect($tokenNames)->toContain('android', 'ios');
});

test('loginMobile fails with invalid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $response = $this->postJson('/api/auth/login-mobile', [
        'email' => 'test@example.com',
        'password' => 'WrongPassword!',
        'device_type' => 'android',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Invalid credentials']);
});

test('loginMobile fails for banned user', function () {
    User::factory()->create([
        'email' => 'banned@example.com',
        'password' => Hash::make('Password123!'),
        'status' => \App\Casts\UserStatusCast::BANNED->value,
    ]);

    $response = $this->postJson('/api/auth/login-mobile', [
        'email' => 'banned@example.com',
        'password' => 'Password123!',
        'device_type' => 'android',
    ]);

    $response->assertStatus(403)
        ->assertJson(['message' => 'Account is banned']);
});

test('loginMobile fails for suspended user', function () {
    User::factory()->create([
        'email' => 'suspended@example.com',
        'password' => Hash::make('Password123!'),
        'status' => \App\Casts\UserStatusCast::SUSPENDED->value,
    ]);

    $response = $this->postJson('/api/auth/login-mobile', [
        'email' => 'suspended@example.com',
        'password' => 'Password123!',
        'device_type' => 'ios',
    ]);

    $response->assertStatus(403)
        ->assertJson(['message' => 'Account is suspended']);
});

// ========================================
// Nuxt Frontend Login Tests (standard /api/auth/login)
// ========================================

test('nuxt login creates token with nuxt name', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    expect($user->fresh()->tokens()->first()->name)->toBe('nuxt');
});

test('nuxt and mobile logins create separate tokens', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    // Login from Nuxt frontend
    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
    ]);

    // Login from Android
    $this->postJson('/api/auth/login-mobile', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'device_type' => 'android',
    ]);

    // Login from iOS
    $this->postJson('/api/auth/login-mobile', [
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'device_type' => 'ios',
    ]);

    $tokenNames = $user->fresh()->tokens()->pluck('name')->toArray();
    expect($tokenNames)->toContain('nuxt', 'android', 'ios')
        ->and(count($tokenNames))->toBe(3);
});
