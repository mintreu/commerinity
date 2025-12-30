<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can set unique code', function () {
    $user = User::factory()->make();
    $user->setUniqueCode('referral_code', 10);

    expect($user->referral_code)->toHaveLength(10);
});

test('can set unique code upper', function () {
    $user = User::factory()->make();
    $user->setUniqueCodeUpper('referral_code', 8);

    expect($user->referral_code)->toMatch('/^[A-Z0-9]{8}$/');
});

test('can set unique uuid', function () {
    $user = User::factory()->make();
    $user->setUniqueUuid('uuid', 16);

    expect($user->uuid)->toHaveLength(16);
});

test('can set unique uuid upper', function () {
    $user = User::factory()->make();
    $user->setUniqueUuidUpper('uuid', 16);

    expect($user->uuid)->toMatch('/^[A-Z0-9]{16}$/');
});
