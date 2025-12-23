<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Geo\Block;
use App\Models\Geo\Country;
use App\Models\Geo\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('address can be created with required fields', function () {
    $country = Country::factory()->india()->create();
    $state = State::factory()->forCountry($country)->create();
    $block = Block::factory()->forState($state)->create();

    $address = Address::factory()->create([
        'title' => 'Home',
        'person_name' => 'John Doe',
        'address_1' => '123 Main St',
        'city' => 'Mumbai',
        'block_id' => $block->id,
        'state_code' => $state->code,
        'country_code' => $country->iso_code_2,
    ]);

    expect($address->title)->toBe('Home')
        ->and($address->person_name)->toBe('John Doe')
        ->and($address->city)->toBe('Mumbai');
});

test('address generates uuid on creation', function () {
    $address = Address::factory()->create();

    expect($address->uuid)->not->toBeNull()
        ->and($address->uuid)->toBeString()
        ->and(strlen($address->uuid))->toBe(36);
});

test('address uses uuid as route key', function () {
    $address = Address::factory()->create();

    expect($address->getRouteKeyName())->toBe('uuid');
});

test('address belongs to addressable polymorphically', function () {
    $user = User::factory()->create();
    $address = Address::factory()->forUser($user)->create();

    expect($address->addressable)->not->toBeNull()
        ->and($address->addressable)->toBeInstanceOf(User::class)
        ->and($address->addressable->id)->toBe($user->id);
});

test('address belongs to block', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();
    $block = Block::factory()->forState($state)->create(['name' => 'Mumbai']);
    $address = Address::factory()->forBlock($block)->create();

    expect($address->block)->not->toBeNull()
        ->and($address->block->name)->toBe('Mumbai');
});

test('address belongs to state', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create(['name' => 'Maharashtra']);
    $address = Address::factory()->create([
        'state_code' => $state->code,
        'country_code' => $country->iso_code_2,
    ]);

    expect($address->state)->not->toBeNull()
        ->and($address->state->name)->toBe('Maharashtra');
});

test('address belongs to country', function () {
    $country = Country::factory()->india()->create();
    $state = State::factory()->forCountry($country)->create();
    $address = Address::factory()->create([
        'country_code' => $country->iso_code_2,
        'state_code' => $state->code,
    ]);

    expect($address->country)->not->toBeNull()
        ->and($address->country->name)->toBe('India');
});

test('address standalone scope filters non-user addresses', function () {
    Address::factory()->warehouse()->create();
    Address::factory()->forUser(User::factory()->create())->create();

    $standalone = Address::standalone()->get();

    expect($standalone)->toHaveCount(1)
        ->and($standalone->first()->addressable_id)->toBeNull();
});

test('address warehouses scope filters warehouse type standalone', function () {
    Address::factory()->warehouse()->create();
    Address::factory()->storeAddress()->create();
    Address::factory()->forUser(User::factory()->create())->home()->create();

    $warehouses = Address::warehouses()->get();

    expect($warehouses)->toHaveCount(1)
        ->and($warehouses->first()->type)->toBe('warehouse');
});

test('address stores scope filters store type standalone', function () {
    Address::factory()->storeAddress()->create();
    Address::factory()->warehouse()->create();

    $stores = Address::stores()->get();

    expect($stores)->toHaveCount(1)
        ->and($stores->first()->type)->toBe('store');
});

test('address userAddresses scope filters user-owned addresses', function () {
    $user = User::factory()->create();
    Address::factory()->forUser($user)->create();
    Address::factory()->warehouse()->create();

    $userAddresses = Address::userAddresses()->get();

    expect($userAddresses)->toHaveCount(1)
        ->and($userAddresses->first()->addressable_type)->toBe(User::class);
});

test('address byType scope filters by type', function () {
    Address::factory()->home()->create();
    Address::factory()->office()->create();

    $homeAddresses = Address::byType('home')->get();

    expect($homeAddresses)->toHaveCount(1)
        ->and($homeAddresses->first()->type)->toBe('home');
});

test('address default scope filters default addresses', function () {
    Address::factory()->default()->create();
    Address::factory()->create(['default' => false]);

    $defaults = Address::default()->get();

    expect($defaults)->toHaveCount(1)
        ->and($defaults->first()->default)->toBeTrue();
});

test('address automatically updates other user addresses when setting new default', function () {
    $user = User::factory()->create();
    $address1 = Address::factory()->forUser($user)->default()->create();
    $address2 = Address::factory()->forUser($user)->create(['default' => false]);

    expect($address1->fresh()->default)->toBeTrue()
        ->and($address2->fresh()->default)->toBeFalse();

    $address2->update(['default' => true]);

    expect($address1->fresh()->default)->toBeFalse()
        ->and($address2->fresh()->default)->toBeTrue();
});

test('address does not update other users addresses when setting default', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $address1 = Address::factory()->forUser($user1)->default()->create();
    $address2 = Address::factory()->forUser($user2)->default()->create();

    expect($address1->fresh()->default)->toBeTrue()
        ->and($address2->fresh()->default)->toBeTrue();
});

test('address standalone default logic works for same type', function () {
    $warehouse1 = Address::factory()->warehouse()->default()->create();
    $warehouse2 = Address::factory()->warehouse()->create(['default' => false]);

    expect($warehouse1->fresh()->default)->toBeTrue()
        ->and($warehouse2->fresh()->default)->toBeFalse();

    $warehouse2->update(['default' => true]);

    expect($warehouse1->fresh()->default)->toBeFalse()
        ->and($warehouse2->fresh()->default)->toBeTrue();
});

test('address getFullAddressAttribute formats address correctly', function () {
    $country = Country::factory()->india()->create();
    $state = State::factory()->forCountry($country)->create(['name' => 'Maharashtra']);
    $address = Address::factory()->create([
        'address_1' => '123 Main St',
        'address_2' => 'Apt 4B',
        'landmark' => 'Near City Mall',
        'city' => 'Mumbai',
        'postal_code' => '400001',
        'state_code' => $state->code,
        'country_code' => $country->iso_code_2,
    ]);

    $fullAddress = $address->full_address;

    expect($fullAddress)->toContain('123 Main St')
        ->and($fullAddress)->toContain('Mumbai')
        ->and($fullAddress)->toContain('Maharashtra')
        ->and($fullAddress)->toContain('India');
});

test('address hasCoordinates returns true when coordinates set', function () {
    $address = Address::factory()->withCoordinates(19.0760, 72.8777)->create();

    expect($address->hasCoordinates())->toBeTrue();
});

test('address hasCoordinates returns false when coordinates null', function () {
    $address = Address::factory()->withoutCoordinates()->create();

    expect($address->hasCoordinates())->toBeFalse();
});

test('address getEffectiveCoordinates returns address coordinates when available', function () {
    $address = Address::factory()->withCoordinates(19.0760, 72.8777)->create();

    $coords = $address->getEffectiveCoordinates();

    expect($coords)->not->toBeNull()
        ->and((string) $coords['latitude'])->toBe('19.07600000')
        ->and((string) $coords['longitude'])->toBe('72.87770000');
});

test('address getEffectiveCoordinates falls back to block coordinates', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();
    $block = Block::factory()->forState($state)->withCoordinates(19.0760, 72.8777)->create();
    $address = Address::factory()->forBlock($block)->withoutCoordinates()->create();

    $coords = $address->getEffectiveCoordinates();

    expect($coords)->not->toBeNull()
        ->and((string) $coords['latitude'])->toBe('19.07600000')
        ->and((string) $coords['longitude'])->toBe('72.87770000');
});

test('address getEffectiveCoordinates returns null when no coordinates available', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();
    $block = Block::factory()->forState($state)->withoutCoordinates()->create();
    $address = Address::factory()->forBlock($block)->withoutCoordinates()->create();

    $coords = $address->getEffectiveCoordinates();

    expect($coords)->toBeNull();
});
