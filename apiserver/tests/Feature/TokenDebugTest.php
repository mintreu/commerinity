<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

test('token deletion actually works', function () {
    $user = User::factory()->create([
        'email' => 'debug@test.com',
        'password' => Hash::make('password'),
    ]);

    // Create token
    $token = $user->createToken('device')->plainTextToken;

    // Verify token exists
    expect(PersonalAccessToken::findToken($token))->not->toBeNull();

    // Delete the token
    $tokenId = explode('|', $token)[0];
    PersonalAccessToken::find($tokenId)->delete();

    // Verify token is gone
    expect(PersonalAccessToken::findToken($token))->toBeNull();

    // Now try to use the deleted token
    $response = $this->withToken($token)->getJson('/api/user');

    // Should get 401
    $response->assertStatus(401);
});
