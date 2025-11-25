<?php

use App\Models\User;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('user can register for spa', function () {
    $response = postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201);
    assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});

test('user can login for spa', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200);
    assertAuthenticatedAs($user);
});

test('user can logout from spa', function () {
    $user = User::factory()->create();
    actingAs($user);

    $response = postJson('/api/v1/auth/logout');

    $response->assertStatus(200);
    assertGuest();
});

test('user can get their own data when authenticated for spa', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->getJson('/api/v1/auth/user');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
});

test('unauthenticated user cannot access spa protected route', function () {
    $response = getJson('/api/v1/auth/user');

    $response->assertStatus(401);
});

test('user can login and get a token', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    $response = postJson('/api/v2/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'test-device',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'token',
            ]
        ]);
});

test('user can access protected route with token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = getJson('/api/v2/auth/user', [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
});

test('user cannot access protected route with invalid token', function () {
    $response = getJson('/api/v2/auth/user', [
        'Authorization' => 'Bearer invalid-token',
    ]);

    $response->assertStatus(401);
});

test('user can logout and revoke token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = postJson('/api/v2/auth/logout', [], [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(200);
    assertDatabaseMissing('personal_access_tokens', [
        'token' => hash('sha256', explode('|', $token)[1]),
    ]);
});
