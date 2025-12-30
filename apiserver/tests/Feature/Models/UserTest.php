<?php

declare(strict_types=1);

use App\Casts\UserStatusCast;
use App\Casts\UserTypeCast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ========================================
// UUID & Referral Code Auto-Generation Tests
// ========================================

test('generates unique UUID on creation with REG prefix and year', function () {
    $user = User::factory()->create();

    expect($user->uuid)
        ->toStartWith('REG'.now()->year)
        ->toMatch('/^REG\d{4}[A-Z0-9]{12}$/'); // REG2025 + 12 random chars
});

test('generates unique referral code on creation', function () {
    $user = User::factory()->create();

    expect($user->referral_code)
        ->toHaveLength(8)
        ->toMatch('/^[A-Z0-9]{8}$/');
});

test('does not regenerate UUID if already set', function () {
    $uuid = 'REG2025CUSTOM0001';
    $user = User::factory()->create(['uuid' => $uuid]);

    expect($user->uuid)->toBe($uuid);
});

test('does not regenerate referral_code if already set', function () {
    $code = 'MYCUSTOM';
    $user = User::factory()->create(['referral_code' => $code]);

    expect($user->referral_code)->toBe($code);
});

test('ensures referral codes are globally unique', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    expect($user1->referral_code)->not->toBe($user2->referral_code);
});

test('ensures UUIDs are globally unique', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    expect($user1->uuid)->not->toBe($user2->uuid);
});

// ========================================
// Type & Status Cast Tests
// ========================================

test('user has default type as regular', function () {
    $user = User::factory()->create();

    expect($user->type)->toBe(UserTypeCast::REGULAR);
});

test('user has default status as draft', function () {
    $user = User::factory()->create();

    expect($user->status)->toBe(UserStatusCast::DRAFT);
});

test('can set user type to member', function () {
    $user = User::factory()->withType(UserTypeCast::MEMBER->value)->create();

    expect($user->type)->toBe(UserTypeCast::MEMBER);
});

test('can set user type to promoter', function () {
    $user = User::factory()->withType(UserTypeCast::PROMOTER->value)->create();

    expect($user->type)->toBe(UserTypeCast::PROMOTER);
});

test('can set user status to active', function () {
    $user = User::factory()->withStatus(UserStatusCast::ACTIVE->value)->create();

    expect($user->status)->toBe(UserStatusCast::ACTIVE);
});

test('can update user type from regular to member', function () {
    $user = User::factory()->create(['type' => UserTypeCast::REGULAR->value]);

    $user->update(['type' => UserTypeCast::MEMBER->value]);

    expect($user->fresh()->type)->toBe(UserTypeCast::MEMBER);
});

test('can update user status from draft to active', function () {
    $user = User::factory()->create(['status' => UserStatusCast::DRAFT->value]);

    $user->update(['status' => UserStatusCast::ACTIVE->value]);

    expect($user->fresh()->status)->toBe(UserStatusCast::ACTIVE);
});

// ========================================
// Affiliate Parent-Child Relationship Tests
// ========================================

test('user can have a parent (upline)', function () {
    $parent = User::factory()->create();
    $child = User::factory()->withParent($parent->id)->create();

    expect($child->parent->id)->toBe($parent->id);
});

test('user can have multiple children (downline)', function () {
    $parent = User::factory()->create();
    $child1 = User::factory()->withParent($parent->id)->create();
    $child2 = User::factory()->withParent($parent->id)->create();

    expect($parent->children)->toHaveCount(2)
        ->and($parent->children->pluck('id'))
        ->toContain($child1->id)
        ->toContain($child2->id);
});

test('user can join Affiliate tree using referral code', function () {
    $parent = User::factory()->create(['referral_code' => 'TESTCODE']);

    // Simulate joining via referral code
    $child = User::factory()->create([
        'parent_id' => $parent->id,
    ]);

    expect($child->parent->referral_code)->toBe('TESTCODE')
        ->and($parent->children->contains($child))->toBeTrue();
});

test('team head has no parent', function () {
    $teamHead = User::factory()->create(['parent_id' => null]);

    expect($teamHead->parent_id)->toBeNull()
        ->and($teamHead->parent)->toBeNull();
});

test('can build multi-level Affiliate tree', function () {
    $level1 = User::factory()->create();                        // Team Head
    $level2 = User::factory()->withParent($level1->id)->create(); // Direct child
    $level3 = User::factory()->withParent($level2->id)->create(); // Grandchild

    expect($level3->parent->id)->toBe($level2->id)
        ->and($level2->parent->id)->toBe($level1->id)
        ->and($level1->parent_id)->toBeNull();
});

// ========================================
// Originator (Agent Recruitment) Tests
// ========================================

test('user can have an originator (agent who recruited them)', function () {
    // Note: We'll need to create an Agent model later
    // For now, testing with polymorphic relationship setup
    $recruiter = User::factory()->withType(UserTypeCast::ADVISOR->value)->create();

    $recruited = User::factory()->create([
        'originator_type' => User::class,
        'originator_id' => $recruiter->id,
    ]);

    expect($recruited->originator)->not->toBeNull()
        ->and($recruited->originator->id)->toBe($recruiter->id);
});

test('agent can recruit member as new team head with no parent', function () {
    $agent = User::factory()->withType(UserTypeCast::ADVISOR->value)->create();

    $recruited = User::factory()->create([
        'originator_type' => User::class,
        'originator_id' => $agent->id,
        'parent_id' => null,
    ]);

    expect($recruited->originator->id)->toBe($agent->id)
        ->and($recruited->parent_id)->toBeNull();
});

test('agent can recruit member under existing user', function () {
    $agent = User::factory()->withType(UserTypeCast::ADVISOR->value)->create();
    $existingUser = User::factory()->withType(UserTypeCast::PROMOTER->value)->create();

    $recruited = User::factory()->create([
        'originator_type' => User::class,
        'originator_id' => $agent->id,
        'parent_id' => $existingUser->id,
    ]);

    expect($recruited->originator->id)->toBe($agent->id)
        ->and($recruited->parent->id)->toBe($existingUser->id);
});

test('agent can track all originated users', function () {
    $agent = User::factory()->withType(UserTypeCast::ADVISOR->value)->create();

    $recruited1 = User::factory()->create([
        'originator_type' => User::class,
        'originator_id' => $agent->id,
    ]);

    $recruited2 = User::factory()->create([
        'originator_type' => User::class,
        'originator_id' => $agent->id,
    ]);

    expect($agent->originatedUsers)->toHaveCount(2)
        ->and($agent->originatedUsers->pluck('id'))
        ->toContain($recruited1->id)
        ->toContain($recruited2->id);
});

test('originator and parent are independent systems', function () {
    $agent = User::factory()->withType(UserTypeCast::ADVISOR->value)->create();
    $affiliateParent = User::factory()->withType(UserTypeCast::PROMOTER->value)->create();

    $recruited = User::factory()->create([
        'originator_type' => User::class,
        'originator_id' => $agent->id,     // Agent recruited this user
        'parent_id' => $affiliateParent->id,     // But placed under different Affiliate parent
    ]);

    // Originator is agent (for salary calculation)
    expect($recruited->originator->id)->toBe($agent->id)
        // Parent is Affiliate upline (for commission calculation)
        ->and($recruited->parent->id)->toBe($affiliateParent->id)
        // Agent is NOT in Affiliate commission tree
        ->and($agent->originatedUsers->contains($recruited))->toBeTrue()
        // But Affiliate parent has recruited in their downline
        ->and($affiliateParent->children->contains($recruited))->toBeTrue();
});

// ========================================
// Onboarding Tests
// ========================================

test('user is not onboarded by default', function () {
    $user = User::factory()->create();

    expect($user->onboarded)->toBeFalse();
});

test('can mark user as onboarded', function () {
    $user = User::factory()->onboarded()->create();

    expect($user->onboarded)->toBeTrue();
});

// ========================================
// Email & Mobile Verification Tests
// ========================================

test('user email is verified by default in factory', function () {
    $user = User::factory()->create();

    expect($user->email_verified_at)->not->toBeNull();
});

test('can create user with unverified email', function () {
    $user = User::factory()->unverified()->create();

    expect($user->email_verified_at)->toBeNull();
});

test('can create user with verified mobile', function () {
    $user = User::factory()->withMobile()->create();

    expect($user->mobile)->not->toBeNull()
        ->and($user->mobile_verified_at)->not->toBeNull();
});

// ========================================
// Data Integrity Tests
// ========================================

test('user can have both email and mobile', function () {
    $user = User::factory()->withMobile()->create();

    expect($user->email)->not->toBeNull()
        ->and($user->mobile)->not->toBeNull();
});

test('user email is unique', function () {
    $user1 = User::factory()->create(['email' => 'test@example.com']);

    expect(fn () => User::factory()->create(['email' => 'test@example.com']))
        ->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

test('user mobile is unique when set', function () {
    $user1 = User::factory()->create(['mobile' => '+1234567890']);

    expect(fn () => User::factory()->create(['mobile' => '+1234567890']))
        ->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

test('user UUID is unique', function () {
    $uuid = 'REG2025UNIQUE0001';
    $user1 = User::factory()->create(['uuid' => $uuid]);

    expect(fn () => User::factory()->create(['uuid' => $uuid]))
        ->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

test('user referral_code is unique', function () {
    $code = 'TESTCODE';
    $user1 = User::factory()->create(['referral_code' => $code]);

    expect(fn () => User::factory()->create(['referral_code' => $code]))
        ->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});
