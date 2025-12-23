<?php

declare(strict_types=1);

use App\Casts\UserStatusCast;
use App\Exceptions\Auth\UserBannedException;
use App\Exceptions\Auth\UserSuspendedException;
use App\Helpers\OtpManager;
use App\Models\User;
use App\Services\UserServices\UserAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->authService = app(UserAuthService::class);
});

// ========================================
// loginWithPassword Tests
// ========================================

test('loginWithPassword returns token for valid email credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $token = $this->authService->loginWithPassword('test@example.com', 'Password123!');

    expect($token)->toBeInstanceOf(NewAccessToken::class)
        ->and($token->plainTextToken)->toBeString()
        ->and($token->plainTextToken)->not->toBeEmpty();
});

test('loginWithPassword returns token for valid mobile credentials', function () {
    $user = User::factory()->create([
        'mobile' => '+919876543210',
        'password' => Hash::make('Password123!'),
    ]);

    $token = $this->authService->loginWithPassword('+919876543210', 'Password123!');

    expect($token)->toBeInstanceOf(NewAccessToken::class)
        ->and($token->plainTextToken)->not->toBeEmpty();
});

test('loginWithPassword returns null for invalid password', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $token = $this->authService->loginWithPassword('test@example.com', 'WrongPassword');

    expect($token)->toBeNull();
});

test('loginWithPassword returns null for non-existent user', function () {
    $token = $this->authService->loginWithPassword('nonexistent@example.com', 'Password123!');

    expect($token)->toBeNull();
});

test('loginWithPassword throws UserBannedException for banned user', function () {
    User::factory()->create([
        'email' => 'banned@example.com',
        'password' => Hash::make('Password123!'),
        'status' => UserStatusCast::BANNED->value,
    ]);

    $this->authService->loginWithPassword('banned@example.com', 'Password123!');
})->throws(UserBannedException::class, 'Account is banned');

test('loginWithPassword throws UserSuspendedException for suspended user', function () {
    User::factory()->create([
        'email' => 'suspended@example.com',
        'password' => Hash::make('Password123!'),
        'status' => UserStatusCast::SUSPENDED->value,
    ]);

    $this->authService->loginWithPassword('suspended@example.com', 'Password123!');
})->throws(UserSuspendedException::class, 'Account is suspended');

test('loginWithPassword uses custom token name', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $this->authService->loginWithPassword('test@example.com', 'Password123!', 'android');

    expect($user->tokens()->first()->name)->toBe('android');
});

test('loginWithPassword uses default api token name', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $this->authService->loginWithPassword('test@example.com', 'Password123!');

    expect($user->tokens()->first()->name)->toBe('api');
});

test('loginWithPassword creates different tokens for different platforms', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $this->authService->loginWithPassword('test@example.com', 'Password123!', 'nuxt');
    $this->authService->loginWithPassword('test@example.com', 'Password123!', 'android');
    $this->authService->loginWithPassword('test@example.com', 'Password123!', 'ios');

    $tokenNames = $user->tokens()->pluck('name')->toArray();
    expect($tokenNames)->toContain('nuxt', 'android', 'ios')
        ->and($user->tokens()->count())->toBe(3);
});

// ========================================
// loginWithOtp Tests
// ========================================

test('loginWithOtp returns token for valid OTP', function () {
    $user = User::factory()->create([
        'mobile' => '+919876543210',
    ]);

    // Generate OTP first
    $otpManager = new OtpManager(
        cache()->store(),
        app('hash'),
        true // Testing mode - OTP is always 123456
    );
    $otpManager->generate('+919876543210');

    $token = $this->authService->loginWithOtp('+919876543210', '123456');

    expect($token)->toBeInstanceOf(NewAccessToken::class)
        ->and($token->plainTextToken)->not->toBeEmpty();
});

test('loginWithOtp returns null for invalid OTP', function () {
    $user = User::factory()->create([
        'mobile' => '+919876543210',
    ]);

    // Generate OTP
    $otpManager = new OtpManager(
        cache()->store(),
        app('hash'),
        true
    );
    $otpManager->generate('+919876543210');

    $token = $this->authService->loginWithOtp('+919876543210', '000000');

    expect($token)->toBeNull();
});

test('loginWithOtp returns null for non-existent user', function () {
    $token = $this->authService->loginWithOtp('nonexistent@example.com', '123456');

    expect($token)->toBeNull();
});

test('loginWithOtp throws UserBannedException for banned user', function () {
    User::factory()->create([
        'mobile' => '+919876543210',
        'status' => UserStatusCast::BANNED->value,
    ]);

    // Generate OTP
    $otpManager = new OtpManager(
        cache()->store(),
        app('hash'),
        true
    );
    $otpManager->generate('+919876543210');

    $this->authService->loginWithOtp('+919876543210', '123456');
})->throws(UserBannedException::class);

test('loginWithOtp throws UserSuspendedException for suspended user', function () {
    User::factory()->create([
        'mobile' => '+919876543210',
        'status' => UserStatusCast::SUSPENDED->value,
    ]);

    // Generate OTP
    $otpManager = new OtpManager(
        cache()->store(),
        app('hash'),
        true
    );
    $otpManager->generate('+919876543210');

    $this->authService->loginWithOtp('+919876543210', '123456');
})->throws(UserSuspendedException::class);

test('loginWithOtp clears OTP after successful login', function () {
    $user = User::factory()->create([
        'mobile' => '+919876543210',
    ]);

    $otpManager = new OtpManager(
        cache()->store(),
        app('hash'),
        true
    );
    $otpManager->generate('+919876543210');

    // First login should succeed
    $token1 = $this->authService->loginWithOtp('+919876543210', '123456');
    expect($token1)->not->toBeNull();

    // Second login with same OTP should fail (OTP cleared)
    $token2 = $this->authService->loginWithOtp('+919876543210', '123456');
    expect($token2)->toBeNull();
});

test('loginWithOtp uses custom token name', function () {
    $user = User::factory()->create([
        'mobile' => '+919876543210',
    ]);

    $otpManager = new OtpManager(
        cache()->store(),
        app('hash'),
        true
    );
    $otpManager->generate('+919876543210');

    $this->authService->loginWithOtp('+919876543210', '123456', 'ios');

    expect($user->tokens()->first()->name)->toBe('ios');
});

// ========================================
// logout Tests
// ========================================

test('logout deletes current token', function () {
    $user = User::factory()->create();
    $tokenObject = $user->createToken('test-device');

    // Create mock request with authenticated user
    $request = Request::create('/api/auth/logout', 'POST');
    $request->setUserResolver(fn () => $user);

    // Set the current access token
    $user->withAccessToken($tokenObject->accessToken);

    $result = $this->authService->logout($request);

    expect($result)->toBeTrue()
        ->and($user->tokens()->count())->toBe(0);
});

test('logout only deletes current token not others', function () {
    $user = User::factory()->create();
    $token1 = $user->createToken('device1');
    $token2 = $user->createToken('device2');

    expect($user->tokens()->count())->toBe(2);

    // Create request with token1 as current
    $request = Request::create('/api/auth/logout', 'POST');
    $request->setUserResolver(fn () => $user);
    $user->withAccessToken($token1->accessToken);

    $this->authService->logout($request);

    // Only token2 should remain
    expect($user->tokens()->count())->toBe(1)
        ->and($user->tokens()->first()->name)->toBe('device2');
});

// ========================================
// logoutAll Tests
// ========================================

test('logoutAll deletes all user tokens', function () {
    $user = User::factory()->create();
    $user->createToken('device1');
    $user->createToken('device2');
    $user->createToken('device3');

    expect($user->tokens()->count())->toBe(3);

    $request = Request::create('/api/auth/logout-all', 'POST');
    $request->setUserResolver(fn () => $user);

    $result = $this->authService->logoutAll($request);

    expect($result)->toBeTrue()
        ->and($user->tokens()->count())->toBe(0);
});

// ========================================
// logoutDeviceType Tests
// ========================================

test('logoutDeviceType deletes only tokens with matching name', function () {
    $user = User::factory()->create();
    $user->createToken('android');
    $user->createToken('android');
    $user->createToken('ios');
    $user->createToken('nuxt');

    expect($user->tokens()->count())->toBe(4);

    $request = Request::create('/api/auth/logout', 'POST');
    $request->setUserResolver(fn () => $user);

    $result = $this->authService->logoutDeviceType($request, 'android');

    expect($result)->toBeTrue()
        ->and($user->tokens()->count())->toBe(2);

    $remainingNames = $user->tokens()->pluck('name')->toArray();
    expect($remainingNames)->toContain('ios', 'nuxt')
        ->and($remainingNames)->not->toContain('android');
});

test('logoutDeviceType handles no matching tokens gracefully', function () {
    $user = User::factory()->create();
    $user->createToken('nuxt');
    $user->createToken('ios');

    $request = Request::create('/api/auth/logout', 'POST');
    $request->setUserResolver(fn () => $user);

    $result = $this->authService->logoutDeviceType($request, 'android');

    expect($result)->toBeTrue()
        ->and($user->tokens()->count())->toBe(2);
});

// ========================================
// findUserByCredential Tests (via loginWithPassword)
// ========================================

test('finds user by email containing @', function () {
    User::factory()->create([
        'email' => 'user@domain.com',
        'password' => Hash::make('password'),
    ]);

    $token = $this->authService->loginWithPassword('user@domain.com', 'password');

    expect($token)->not->toBeNull();
});

test('finds user by mobile not containing @', function () {
    User::factory()->create([
        'mobile' => '+919876543210',
        'password' => Hash::make('password'),
    ]);

    $token = $this->authService->loginWithPassword('+919876543210', 'password');

    expect($token)->not->toBeNull();
});

test('does not find user with email when searching by mobile', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'mobile' => null,
        'password' => Hash::make('password'),
    ]);

    // Search with a string that doesn't contain @ (treated as mobile)
    $token = $this->authService->loginWithPassword('1234567890', 'password');

    expect($token)->toBeNull();
});

// ========================================
// Edge Cases
// ========================================

test('handles user with both email and mobile', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'mobile' => '+919876543210',
        'password' => Hash::make('password'),
    ]);

    // Can login with email
    $tokenByEmail = $this->authService->loginWithPassword('test@example.com', 'password');
    expect($tokenByEmail)->not->toBeNull();

    // Can login with mobile
    $tokenByMobile = $this->authService->loginWithPassword('+919876543210', 'password');
    expect($tokenByMobile)->not->toBeNull();

    expect($user->tokens()->count())->toBe(2);
});

test('active user status allows login', function () {
    $user = User::factory()->create([
        'email' => 'active@example.com',
        'password' => Hash::make('password'),
        'status' => UserStatusCast::ACTIVE->value,
    ]);

    $token = $this->authService->loginWithPassword('active@example.com', 'password');

    expect($token)->not->toBeNull();
});

test('pending user status allows login', function () {
    $user = User::factory()->create([
        'email' => 'pending@example.com',
        'password' => Hash::make('password'),
        'status' => UserStatusCast::PENDING->value,
    ]);

    $token = $this->authService->loginWithPassword('pending@example.com', 'password');

    expect($token)->not->toBeNull();
});

test('empty password returns null', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $token = $this->authService->loginWithPassword('test@example.com', '');

    expect($token)->toBeNull();
});

test('password is case sensitive', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $token = $this->authService->loginWithPassword('test@example.com', 'password123!');

    expect($token)->toBeNull();
});

test('email lookup is case insensitive', function () {
    User::factory()->create([
        'email' => 'Test@Example.COM',
        'password' => Hash::make('password'),
    ]);

    // Laravel/MySQL typically handles this case-insensitively
    $token = $this->authService->loginWithPassword('test@example.com', 'password');

    expect($token)->not->toBeNull();
});
