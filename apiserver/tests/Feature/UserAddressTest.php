<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can have multiple addresses', function () {
    $user = User::factory()->create();
    Address::factory()->forUser($user)->count(3)->create();

    expect($user->addresses)->toHaveCount(3);
});

test('user can set default address', function () {
    $user = User::factory()->create();
    $home = Address::factory()->forUser($user)->home()->default()->create();
    $office = Address::factory()->forUser($user)->office()->create();

    expect($user->defaultAddress()->id)->toBe($home->id);
});

test('user default address automatically switches when new default is set', function () {
    $user = User::factory()->create();
    $home = Address::factory()->forUser($user)->home()->default()->create();
    $office = Address::factory()->forUser($user)->office()->create();

    expect($user->defaultAddress()->id)->toBe($home->id);

    $office->update(['default' => true]);

    expect($user->fresh()->defaultAddress()->id)->toBe($office->id)
        ->and($home->fresh()->default)->toBeFalse();
});

test('user onboarding requires address', function () {
    $user = User::factory()->create([
        'onboarded' => true,
        'email_verified_at' => now(),
    ]);

    expect($user->isOnboardingComplete())->toBeFalse();

    Address::factory()->forUser($user)->create();

    expect($user->fresh()->isOnboardingComplete())->toBeTrue();
});

test('user onboarding requires email or mobile verification', function () {
    $user = User::factory()->create([
        'onboarded' => true,
        'email_verified_at' => null,
        'mobile_verified_at' => null,
    ]);
    Address::factory()->forUser($user)->create();

    expect($user->isOnboardingComplete())->toBeFalse();

    $user->update(['email_verified_at' => now()]);

    expect($user->fresh()->isOnboardingComplete())->toBeTrue();
});

test('user can have mlm parent relationship', function () {
    $parent = User::factory()->create();
    $child = User::factory()->create(['parent_id' => $parent->id]);

    expect($child->parent->id)->toBe($parent->id)
        ->and($parent->children)->toHaveCount(1)
        ->and($parent->children->first()->id)->toBe($child->id);
});

test('user can have multiple mlm children', function () {
    $parent = User::factory()->create();
    User::factory()->count(5)->create(['parent_id' => $parent->id]);

    expect($parent->children)->toHaveCount(5);
});

test('user auto generates uuid on creation', function () {
    $user = User::factory()->create();

    expect($user->uuid)->not->toBeNull()
        ->and($user->uuid)->toBeString()
        ->and($user->uuid)->toStartWith('REG'.now()->year);
});

test('user auto generates unique referral code on creation', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    expect($user1->referral_code)->not->toBeNull()
        ->and($user1->referral_code)->toBeString()
        ->and(strlen($user1->referral_code))->toBe(8)
        ->and($user1->referral_code)->not->toBe($user2->referral_code);
});

test('user uses uuid as route key', function () {
    $user = User::factory()->create();

    expect($user->getRouteKeyName())->toBe('uuid');
});

test('user has verified mobile check method', function () {
    $user = User::factory()->create(['mobile_verified_at' => null]);
    expect($user->hasVerifiedMobile())->toBeFalse();

    $user->update(['mobile_verified_at' => now()]);
    expect($user->fresh()->hasVerifiedMobile())->toBeTrue();
});

test('user can have originator morphTo relationship', function () {
    $agent = User::factory()->create();
    $member = User::factory()->create([
        'originator_type' => User::class,
        'originator_id' => $agent->id,
    ]);

    expect($member->originator)->not->toBeNull()
        ->and($member->originator->id)->toBe($agent->id);
});

test('user can have originated users morphMany relationship', function () {
    $agent = User::factory()->create();
    User::factory()->count(3)->create([
        'originator_type' => User::class,
        'originator_id' => $agent->id,
    ]);

    expect($agent->originatedUsers)->toHaveCount(3);
});

test('user address deletion works with soft deletes', function () {
    $user = User::factory()->create();
    $address = Address::factory()->forUser($user)->create();
    $addressId = $address->id;

    $address->delete();

    expect($user->addresses()->withTrashed()->count())->toBe(1)
        ->and(Address::withTrashed()->find($addressId))->not->toBeNull()
        ->and(Address::find($addressId))->toBeNull();
});

test('user can restore soft deleted address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->forUser($user)->create();

    $address->delete();
    expect($user->addresses()->count())->toBe(0);

    $address->restore();
    expect($user->fresh()->addresses()->count())->toBe(1);
});
