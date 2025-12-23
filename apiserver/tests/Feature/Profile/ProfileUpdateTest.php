<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'mobile' => '+919876543210',
        'email' => 'john@example.com',
        'bio' => 'Original bio',
        'dob' => '1990-01-15',
    ]);
});

it('can update user profile with all fields', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'bio' => 'Updated bio text',
        'gender' => 'female',
        'dob' => '1992-05-20',
    ]);

    $response->assertSuccessful();
    // Email change requires verification, so message indicates pending verification
    $response->assertJsonFragment([
        'pending_verification' => ['email'],
    ]);

    $this->user->refresh();

    expect($this->user->name)->toBe('Jane Smith');
    // Email is NOT directly updated - requires verification
    expect($this->user->email)->toBe('john@example.com');
    expect($this->user->bio)->toBe('Updated bio text');
    expect($this->user->gender->value)->toBe('female');
    expect($this->user->dob->format('Y-m-d'))->toBe('1992-05-20');
});

it('can update profile with only required fields', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'Updated Name',
    ]);

    $response->assertSuccessful();
    $this->user->refresh();

    expect($this->user->name)->toBe('Updated Name');
    expect($this->user->email)->toBe('john@example.com'); // Unchanged
});

it('can update profile with nullable email - keeps existing email', function () {
    // When passing null for email, the existing email is kept (not cleared)
    // This is by design - clearing email requires explicit action
    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'John Doe',
        'email' => null,
    ]);

    $response->assertSuccessful();
    $this->user->refresh();

    // Email is kept as is when null is passed (no change means no verification needed)
    expect($this->user->email)->toBe('john@example.com');
});

it('requires authentication to update profile', function () {
    $response = $this->putJson('/api/user/profile', [
        'name' => 'Unauthorized User',
    ]);

    $response->assertUnauthorized();
});

it('requires name field', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'email' => 'test@example.com',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name']);
});

it('validates email format', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'John Doe',
        'email' => 'invalid-email',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});

it('ensures email uniqueness', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'John Doe',
        'email' => 'taken@example.com',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});

it('allows keeping same email', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'John Doe Updated',
        'email' => 'john@example.com', // Same email
    ]);

    $response->assertSuccessful();
});

it('validates bio max length', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'John Doe',
        'bio' => str_repeat('a', 501), // 501 characters
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['bio']);
});

it('validates gender values', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'John Doe',
        'gender' => 'invalid',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['gender']);
});

it('accepts valid gender values', function ($gender) {
    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'John Doe',
        'gender' => $gender,
    ]);

    $response->assertSuccessful();
    $this->user->refresh();
    expect($this->user->gender->value)->toBe($gender);
})->with(['male', 'female', 'other']);

it('validates date of birth is in the past', function () {
    $futureDate = now()->addDays(1)->format('Y-m-d');

    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'John Doe',
        'dob' => $futureDate,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['dob']);
});

it('validates date of birth format', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'John Doe',
        'dob' => 'invalid-date',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['dob']);
});

it('returns updated user data in response', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);

    $response->assertSuccessful();
    $response->assertJsonPath('data.user.name', 'Updated Name');
    // Email is NOT updated immediately - requires verification
    $response->assertJsonPath('data.user.email', 'john@example.com');
    // Should indicate email is pending verification
    $response->assertJsonFragment(['pending_verification' => ['email']]);
});

it('updates email directly when same email is provided', function () {
    $response = $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'Updated Name',
        'email' => 'john@example.com', // Same email as user has
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'message' => 'Profile updated successfully.',
    ]);
    // No pending verification since email didn't change
    $response->assertJsonMissing(['pending_verification']);
});

it('does not change mobile number', function () {
    $originalMobile = $this->user->mobile;

    $this->actingAs($this->user)->putJson('/api/user/profile', [
        'name' => 'Updated Name',
    ]);

    $this->user->refresh();
    expect($this->user->mobile)->toBe($originalMobile);
});
