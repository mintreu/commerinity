<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Geo\Country;
use App\Models\Geo\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $country = Country::factory()->india()->create();
    State::factory()->forCountry($country)->create(['code' => 'WB', 'name' => 'West Bengal']);
});

test('authenticated user can list their addresses', function () {
    $user = User::factory()->create();
    Address::factory()->count(3)->for($user, 'addressable')->create();

    $response = $this->actingAs($user)->getJson('/api/addresses');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('first address is automatically set as default', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/addresses', [
        'person_name' => 'John Doe',
        'person_mobile' => '+919876543210',
        'type' => 'home',
        'address_1' => '123 Main St',
        'city' => 'Kolkata',
        'postal_code' => '700001',
        'state_code' => 'WB',
        'country_code' => 'IN',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.default', true);
});

test('user can create new address', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/addresses', [
        'title' => 'Home',
        'person_name' => 'John Doe',
        'person_mobile' => '+919876543210',
        'type' => 'home',
        'address_1' => '123 Main St',
        'address_2' => 'Apt 4B',
        'landmark' => 'Near Park',
        'city' => 'Kolkata',
        'postal_code' => '700001',
        'state_code' => 'WB',
        'country_code' => 'IN',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => [
                'uuid', 'title', 'person_name', 'type',
                'address_1', 'city', 'postal_code', 'default',
                'country' => ['code', 'name'],
                'state' => ['code', 'name'],
            ],
        ]);
});

test('setting address as default unsets other defaults', function () {
    $user = User::factory()->create();
    $address1 = Address::factory()->for($user, 'addressable')->create(['default' => true]);
    $address2 = Address::factory()->for($user, 'addressable')->create(['default' => false]);

    $response = $this->actingAs($user)->postJson("/api/addresses/{$address2->uuid}/default");

    $response->assertSuccessful();
    expect($address1->fresh()->default)->toBeFalse();
    expect($address2->fresh()->default)->toBeTrue();
});

test('user can view specific address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user, 'addressable')->create();

    $response = $this->actingAs($user)->getJson("/api/addresses/{$address->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $address->uuid);
});

test('user cannot view another users address', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $address = Address::factory()->for($user2, 'addressable')->create();

    $response = $this->actingAs($user1)->getJson("/api/addresses/{$address->uuid}");

    $response->assertNotFound();
});

test('user can update their address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user, 'addressable')->create();

    $response = $this->actingAs($user)->putJson("/api/addresses/{$address->uuid}", [
        'city' => 'Mumbai',
        'postal_code' => '400001',
    ]);

    $response->assertSuccessful();
    expect($address->fresh()->city)->toBe('Mumbai');
});

test('user cannot update another users address', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $address = Address::factory()->for($user2, 'addressable')->create();

    $response = $this->actingAs($user1)->putJson("/api/addresses/{$address->uuid}", [
        'city' => 'Mumbai',
    ]);

    $response->assertNotFound();
});

test('user can delete their address', function () {
    $user = User::factory()->create();
    $address = Address::factory()->for($user, 'addressable')->create();

    $response = $this->actingAs($user)->deleteJson("/api/addresses/{$address->uuid}");

    $response->assertSuccessful();
    expect(Address::find($address->id))->toBeNull();
});

test('user cannot delete another users address', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $address = Address::factory()->for($user2, 'addressable')->create();

    $response = $this->actingAs($user1)->deleteJson("/api/addresses/{$address->uuid}");

    $response->assertNotFound();
});

test('validation requires person name', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/addresses', [
        'person_mobile' => '+919876543210',
        'type' => 'home',
        'address_1' => '123 Main St',
        'city' => 'Kolkata',
        'postal_code' => '700001',
        'state_code' => 'WB',
        'country_code' => 'IN',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('person_name');
});

test('validation requires valid mobile format', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/addresses', [
        'person_name' => 'John Doe',
        'person_mobile' => '1234567890',
        'type' => 'home',
        'address_1' => '123 Main St',
        'city' => 'Kolkata',
        'postal_code' => '700001',
        'state_code' => 'WB',
        'country_code' => 'IN',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('person_mobile');
});

test('validation requires valid address type', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/addresses', [
        'person_name' => 'John Doe',
        'person_mobile' => '+919876543210',
        'type' => 'invalid',
        'address_1' => '123 Main St',
        'city' => 'Kolkata',
        'postal_code' => '700001',
        'state_code' => 'WB',
        'country_code' => 'IN',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('type');
});

test('addresses are ordered by default first', function () {
    $user = User::factory()->create();
    $address1 = Address::factory()->for($user, 'addressable')->create(['default' => false]);
    $address2 = Address::factory()->for($user, 'addressable')->create(['default' => true]);

    $response = $this->actingAs($user)->getJson('/api/addresses');

    $response->assertSuccessful();
    $data = $response->json('data');
    expect($data[0]['uuid'])->toBe($address2->uuid);
});

test('unauthenticated user cannot access addresses', function () {
    $response = $this->getJson('/api/addresses');

    $response->assertUnauthorized();
});
