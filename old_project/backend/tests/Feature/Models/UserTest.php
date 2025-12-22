<?php

use App\Models\Lifecycle\Level;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can be created with the factory', function () {
    $user = User::factory()->create();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => $user->email,
    ]);
});

test('a user has a level', function () {
    $level = Level::factory()->create();
    $user = User::factory()->for($level)->create();

    expect($user->level)->toBeInstanceOf(Level::class);
});
