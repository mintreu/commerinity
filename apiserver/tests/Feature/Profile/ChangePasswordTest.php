<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);
});

it('can change password with correct current password', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'message' => 'Password changed successfully.',
    ]);

    $this->user->refresh();
    expect(Hash::check('new-password-123', $this->user->password))->toBeTrue();
});

it('fails with incorrect current password', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/password', [
        'current_password' => 'wrong-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertUnprocessable();
    $response->assertJson([
        'message' => 'The current password is incorrect.',
    ]);
});

it('requires authentication', function () {
    $response = $this->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertUnauthorized();
});

it('requires current password field', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/password', [
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['current_password']);
});

it('requires new password field', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password']);
});

it('requires password confirmation', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-123',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password']);
});

it('validates password confirmation matches', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password']);
});

it('requires minimum 8 characters for new password', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password']);
});

it('can logout other devices when changing password', function () {
    // Create multiple tokens for the user
    $token1 = $this->user->createToken('device1');
    $token2 = $this->user->createToken('device2');
    $currentTokenObject = $this->user->createToken('current-device');

    expect($this->user->tokens()->count())->toBe(3);

    // Use withToken() to simulate actual token auth
    $response = $this->withToken($currentTokenObject->plainTextToken)->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
        'logout_other_devices' => true,
    ]);

    $response->assertSuccessful();

    // Only current token should remain
    $this->user->refresh();
    expect($this->user->tokens()->count())->toBe(1);
});

it('keeps all tokens when logout_other_devices is false', function () {
    $token1 = $this->user->createToken('device1')->plainTextToken;
    $token2 = $this->user->createToken('device2')->plainTextToken;

    expect($this->user->tokens()->count())->toBe(2);

    $response = $this->actingAs($this->user)->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
        'logout_other_devices' => false,
    ]);

    $response->assertSuccessful();

    // All tokens should remain
    $this->user->refresh();
    expect($this->user->tokens()->count())->toBe(2);
});

it('keeps all tokens by default when logout_other_devices not provided', function () {
    $token1 = $this->user->createToken('device1')->plainTextToken;
    $token2 = $this->user->createToken('device2')->plainTextToken;

    $response = $this->actingAs($this->user)->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertSuccessful();
    expect($this->user->tokens()->count())->toBe(2);
});

it('hashes the new password', function () {
    $this->actingAs($this->user)->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $this->user->refresh();

    // Password should be hashed, not plain text
    expect($this->user->password)->not->toBe('new-password-123');
    expect(Hash::check('new-password-123', $this->user->password))->toBeTrue();
});

it('can use new password to login after change', function () {
    // Ensure user has an email for login
    $this->user->update(['email' => 'test@example.com']);

    // Change password
    $this->actingAs($this->user)->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    // Try logging in with new password (using email since mobile requires OTP)
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'new-password-123',
    ]);

    $response->assertSuccessful();
    expect($response->json('token'))->not->toBeEmpty();
});

it('cannot use old password after change', function () {
    // Ensure user has an email for login
    $this->user->update(['email' => 'test@example.com']);

    // Change password
    $this->actingAs($this->user)->putJson('/api/user/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    // Try logging in with old password (using email since mobile requires OTP)
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'old-password',
    ]);

    $response->assertUnauthorized();
});
