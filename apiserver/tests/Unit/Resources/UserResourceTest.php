<?php

declare(strict_types=1);

use App\Casts\GenderCast;
use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

// ========================================
// Basic Output Format Tests
// ========================================

test('resource returns correct structure', function () {
    $user = User::factory()->create();
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array)->toHaveKeys([
        'uuid',
        'name',
        'email',
        'mobile',
        'email_verified',
        'mobile_verified',
        'referral_code',
        'hasParent',
        'gender',
        'dob',
        'bio',
        'type',
        'status',
        'avatar',
        'hasLevel',
        'level_id',
        'onboarded',
    ]);
});

test('resource returns uuid correctly', function () {
    $user = User::factory()->create(['uuid' => 'REG2025ABCDEFGH1234']);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['uuid'])->toBe('REG2025ABCDEFGH1234');
});

test('resource returns name correctly', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['name'])->toBe('John Doe');
});

test('resource returns email correctly', function () {
    $user = User::factory()->create(['email' => 'john@example.com']);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['email'])->toBe('john@example.com');
});

test('resource returns mobile correctly', function () {
    $user = User::factory()->create(['mobile' => '+919876543210']);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['mobile'])->toBe('+919876543210');
});

// ========================================
// Email/Mobile Verification Tests
// ========================================

test('email_verified is true when email is verified', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['email_verified'])->toBeTrue();
});

test('email_verified is false when email is not verified', function () {
    $user = User::factory()->create(['email_verified_at' => null]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['email_verified'])->toBeFalse();
});

test('mobile_verified is true when mobile is verified', function () {
    $user = User::factory()->create(['mobile_verified_at' => now()]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['mobile_verified'])->toBeTrue();
});

test('mobile_verified is false when mobile is not verified', function () {
    $user = User::factory()->create(['mobile_verified_at' => null]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['mobile_verified'])->toBeFalse();
});

// ========================================
// Parent Relationship Tests
// ========================================

test('hasParent is true when user has parent', function () {
    $parent = User::factory()->create();
    $user = User::factory()->create(['parent_id' => $parent->id]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['hasParent'])->toBeTrue();
});

test('hasParent is false when user has no parent', function () {
    $user = User::factory()->create(['parent_id' => null]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['hasParent'])->toBeFalse();
});

test('parent data is included when parent exists and loaded', function () {
    $parent = User::factory()->create([
        'uuid' => 'PARENT123456789',
        'name' => 'Parent User',
    ]);
    $user = User::factory()->create(['parent_id' => $parent->id]);
    $user->load('parent');

    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['parent'])->toBeArray()
        ->and($array['parent']['uuid'])->toBe('PARENT123456789')
        ->and($array['parent']['name'])->toBe('Parent User');
});

// ========================================
// Enum Value Tests
// ========================================

test('type returns enum value as string', function () {
    $user = User::factory()->create(['type' => UserTypeCast::REGULAR->value]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['type'])->toBe('regular');
});

test('status returns enum value as string', function () {
    $user = User::factory()->create(['status' => UserStatusCast::ACTIVE->value]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['status'])->toBe('active');
});

test('gender returns enum value when set', function () {
    $user = User::factory()->create(['gender' => GenderCast::MALE->value]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['gender'])->toBe('male');
});

test('gender returns enum value for all genders', function ($gender, $expected) {
    $user = User::factory()->create(['gender' => $gender]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['gender'])->toBe($expected);
})->with([
    ['male', 'male'],
    ['female', 'female'],
    ['other', 'other'],
]);

// ========================================
// Date Format Tests
// ========================================

test('dob returns formatted date when set', function () {
    $user = User::factory()->create(['dob' => '1990-05-15']);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['dob'])->toBe('1990-05-15');
});

test('dob returns null when not set', function () {
    $user = User::factory()->create(['dob' => null]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['dob'])->toBeNull();
});

// ========================================
// Optional Fields Tests
// ========================================

test('bio returns value when set', function () {
    $user = User::factory()->create(['bio' => 'A short biography']);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['bio'])->toBe('A short biography');
});

test('bio returns null when not set', function () {
    $user = User::factory()->create(['bio' => null]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['bio'])->toBeNull();
});

test('onboarded returns boolean', function () {
    $user = User::factory()->create(['onboarded' => true]);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['onboarded'])->toBe(true);
});

// ========================================
// Referral Code Tests
// ========================================

test('referral_code returns user referral code', function () {
    $user = User::factory()->create(['referral_code' => 'ABCD1234']);
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['referral_code'])->toBe('ABCD1234');
});

// ========================================
// Level Fields Tests
// ========================================

test('hasLevel is always false (TODO)', function () {
    $user = User::factory()->create();
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['hasLevel'])->toBeFalse();
});

test('level_id is always null (TODO)', function () {
    $user = User::factory()->create();
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['level_id'])->toBeNull();
});

// ========================================
// Avatar Tests
// ========================================

test('avatar returns empty string when no avatar', function () {
    $user = User::factory()->create();
    $resource = new UserResource($user);
    $array = $resource->toArray(new Request);

    expect($array['avatar'])->toBe('');
});
