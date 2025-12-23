<?php

declare(strict_types=1);

use App\Casts\GenderCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Models\Address;
use App\Models\Kyc;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

// ========================================
// UUID Generation Tests
// ========================================

test('uuid is auto-generated on creation', function () {
    $user = User::factory()->create(['uuid' => null]);

    // UUID format: REG + year (4 chars) + random (12 chars) = 19 chars
    expect($user->uuid)->not->toBeNull()
        ->and(strlen($user->uuid))->toBe(19);
});

test('uuid starts with REG prefix and current year', function () {
    $user = User::factory()->create(['uuid' => null]);
    $currentYear = now()->year;

    expect($user->uuid)->toStartWith('REG'.$currentYear);
});

test('uuid is unique for each user', function () {
    $users = User::factory()->count(10)->create();
    $uuids = $users->pluck('uuid')->toArray();

    expect(count(array_unique($uuids)))->toBe(10);
});

test('uuid can be manually set', function () {
    $customUuid = 'CUSTOM1234567890';
    $user = User::factory()->create(['uuid' => $customUuid]);

    expect($user->uuid)->toBe($customUuid);
});

// ========================================
// Referral Code Generation Tests
// ========================================

test('referral_code is auto-generated on creation', function () {
    $user = User::factory()->create(['referral_code' => null]);

    expect($user->referral_code)->not->toBeNull()
        ->and(strlen($user->referral_code))->toBe(8);
});

test('referral_code is uppercase', function () {
    $user = User::factory()->create(['referral_code' => null]);

    expect($user->referral_code)->toBe(Str::upper($user->referral_code));
});

test('referral_code is unique for each user', function () {
    $users = User::factory()->count(20)->create();
    $codes = $users->pluck('referral_code')->toArray();

    expect(count(array_unique($codes)))->toBe(20);
});

test('referral_code can be manually set', function () {
    $customCode = 'CUSTOM12';
    $user = User::factory()->create(['referral_code' => $customCode]);

    expect($user->referral_code)->toBe($customCode);
});

// ========================================
// Casts Tests
// ========================================

test('status is cast to UserStatusCast enum', function () {
    $user = User::factory()->create(['status' => 'active']);

    expect($user->status)->toBe(UserStatusCast::ACTIVE);
});

test('type is cast to UserTypeCast enum', function () {
    $user = User::factory()->create(['type' => 'regular']);

    expect($user->type)->toBe(UserTypeCast::REGULAR);
});

test('gender is cast to GenderCast enum', function () {
    $user = User::factory()->create(['gender' => 'male']);

    expect($user->gender)->toBe(GenderCast::MALE);
});

test('email_verified_at is cast to datetime', function () {
    $user = User::factory()->create(['email_verified_at' => '2024-01-15 10:30:00']);

    expect($user->email_verified_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('mobile_verified_at is cast to datetime', function () {
    $user = User::factory()->create(['mobile_verified_at' => now()]);

    expect($user->mobile_verified_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('dob is cast to date', function () {
    $user = User::factory()->create(['dob' => '1990-05-15']);

    expect($user->dob)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('onboarded is cast to boolean', function () {
    $user = User::factory()->create(['onboarded' => 1]);

    expect($user->onboarded)->toBe(true);
});

test('password is hidden from array', function () {
    $user = User::factory()->create(['password' => 'secret']);
    $array = $user->toArray();

    expect($array)->not->toHaveKey('password');
});

// ========================================
// Relationship Tests
// ========================================

test('user can have a parent (upline)', function () {
    $parent = User::factory()->create();
    $child = User::factory()->create(['parent_id' => $parent->id]);

    expect($child->parent->id)->toBe($parent->id);
});

test('user can have children (downline)', function () {
    $parent = User::factory()->create();
    User::factory()->count(3)->create(['parent_id' => $parent->id]);

    expect($parent->children)->toHaveCount(3);
});

test('user can have addresses', function () {
    $user = User::factory()->create();
    Address::factory()->count(2)->create([
        'addressable_type' => User::class,
        'addressable_id' => $user->id,
    ]);

    expect($user->addresses)->toHaveCount(2);
});

test('user can have single kyc record', function () {
    $user = User::factory()->create();
    Kyc::factory()->create([
        'kycable_type' => User::class,
        'kycable_id' => $user->id,
    ]);

    expect($user->kyc)->not->toBeNull()
        ->and($user->kyc)->toBeInstanceOf(Kyc::class);
});

test('user can have multiple kyc records (history)', function () {
    $user = User::factory()->create();
    Kyc::factory()->count(3)->create([
        'kycable_type' => User::class,
        'kycable_id' => $user->id,
    ]);

    expect($user->kycs)->toHaveCount(3);
});

// ========================================
// hasVerifiedMobile Tests
// ========================================

test('hasVerifiedMobile returns true when mobile is verified', function () {
    $user = User::factory()->create(['mobile_verified_at' => now()]);

    expect($user->hasVerifiedMobile())->toBeTrue();
});

test('hasVerifiedMobile returns false when mobile is not verified', function () {
    $user = User::factory()->create(['mobile_verified_at' => null]);

    expect($user->hasVerifiedMobile())->toBeFalse();
});

// ========================================
// hasVerifiedEmail Tests (inherited from MustVerifyEmail)
// ========================================

test('hasVerifiedEmail returns true when email is verified', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    expect($user->hasVerifiedEmail())->toBeTrue();
});

test('hasVerifiedEmail returns false when email is not verified', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    expect($user->hasVerifiedEmail())->toBeFalse();
});

// ========================================
// hasApprovedKyc Tests
// ========================================

test('hasApprovedKyc returns true when user has approved KYC', function () {
    $user = User::factory()->create();
    Kyc::factory()->approved()->create([
        'kycable_type' => User::class,
        'kycable_id' => $user->id,
    ]);

    expect($user->hasApprovedKyc())->toBeTrue();
});

test('hasApprovedKyc returns false when user has pending KYC', function () {
    $user = User::factory()->create();
    Kyc::factory()->pending()->create([
        'kycable_type' => User::class,
        'kycable_id' => $user->id,
    ]);

    expect($user->hasApprovedKyc())->toBeFalse();
});

test('hasApprovedKyc returns false when user has no KYC', function () {
    $user = User::factory()->create();

    expect($user->hasApprovedKyc())->toBeFalse();
});

// ========================================
// defaultAddress Tests
// ========================================

test('defaultAddress returns default address when set', function () {
    $user = User::factory()->create();
    $defaultAddress = Address::factory()->create([
        'addressable_type' => User::class,
        'addressable_id' => $user->id,
        'default' => true,
    ]);
    Address::factory()->create([
        'addressable_type' => User::class,
        'addressable_id' => $user->id,
        'default' => false,
    ]);

    expect($user->defaultAddress()->id)->toBe($defaultAddress->id);
});

test('defaultAddress returns null when no default set', function () {
    $user = User::factory()->create();

    // Create address explicitly without default state
    $address = Address::factory()->create([
        'addressable_type' => User::class,
        'addressable_id' => $user->id,
    ]);

    // Force the address to be non-default
    $address->update(['default' => false]);

    expect($user->fresh()->defaultAddress())->toBeNull();
});

// ========================================
// isOnboardingComplete Tests
// ========================================

test('isOnboardingComplete returns true when all conditions met', function () {
    $user = User::factory()->create([
        'onboarded' => true,
        'email_verified_at' => now(),
    ]);
    Address::factory()->create([
        'addressable_type' => User::class,
        'addressable_id' => $user->id,
    ]);

    expect($user->isOnboardingComplete())->toBeTrue();
});

test('isOnboardingComplete returns true with mobile verified instead of email', function () {
    $user = User::factory()->create([
        'onboarded' => true,
        'email_verified_at' => null,
        'mobile_verified_at' => now(),
    ]);
    Address::factory()->create([
        'addressable_type' => User::class,
        'addressable_id' => $user->id,
    ]);

    expect($user->isOnboardingComplete())->toBeTrue();
});

test('isOnboardingComplete returns false when not onboarded', function () {
    $user = User::factory()->create([
        'onboarded' => false,
        'email_verified_at' => now(),
    ]);
    Address::factory()->create([
        'addressable_type' => User::class,
        'addressable_id' => $user->id,
    ]);

    expect($user->isOnboardingComplete())->toBeFalse();
});

test('isOnboardingComplete returns false when no address', function () {
    $user = User::factory()->create([
        'onboarded' => true,
        'email_verified_at' => now(),
    ]);

    expect($user->isOnboardingComplete())->toBeFalse();
});

test('isOnboardingComplete returns false when neither email nor mobile verified', function () {
    $user = User::factory()->create([
        'onboarded' => true,
        'email_verified_at' => null,
        'mobile_verified_at' => null,
    ]);
    Address::factory()->create([
        'addressable_type' => User::class,
        'addressable_id' => $user->id,
    ]);

    expect($user->isOnboardingComplete())->toBeFalse();
});

// ========================================
// Route Key Tests
// ========================================

test('route key name is uuid', function () {
    $user = new User;

    expect($user->getRouteKeyName())->toBe('uuid');
});

// ========================================
// Edge Cases
// ========================================

test('user without parent has null parent relationship', function () {
    $user = User::factory()->create(['parent_id' => null]);

    expect($user->parent)->toBeNull();
});

test('user without children has empty children collection', function () {
    $user = User::factory()->create();

    expect($user->children)->toBeEmpty();
});

test('user can have both email and mobile verified', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'mobile_verified_at' => now(),
    ]);

    expect($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->hasVerifiedMobile())->toBeTrue();
});
