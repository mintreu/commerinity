<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('generates fingerprint for user', function () {
    $user = User::factory()->create();

    expect($user->fingerprint())->not->toBeNull()
        ->and(strlen($user->fingerprint()))->toBe(16);
});

test('generates full fingerprint for user', function () {
    $user = User::factory()->create();

    expect($user->fingerprintFull())->not->toBeNull()
        ->and(strlen($user->fingerprintFull()))->toBeLessThanOrEqual(64);
});

test('matches valid fingerprint', function () {
    $user = User::factory()->create();
    $fingerprint = $user->fingerprint();

    expect($user->matchesFingerprint($fingerprint))->toBeTrue();
});

test('does not match invalid fingerprint', function () {
    $user = User::factory()->create();

    expect($user->matchesFingerprint('invalid-fingerprint'))->toBeFalse();
});

test('fingerprint is deterministic for same user data', function () {
    $user = User::factory()->create(['uuid' => 'REG2025DETERM123']);
    $fp1 = $user->fingerprint();

    // Refresh user to ensure it's the same state
    $user = $user->fresh();
    $fp2 = $user->fingerprint();

    expect($fp1)->toBe($fp2);
});
