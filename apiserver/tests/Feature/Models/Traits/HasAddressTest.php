<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\User;
use App\Casts\AddressTypeCast;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can have addresses via HasAddress trait', function () {
    $user = User::factory()->create();
    $address = Address::factory()->forUser($user)->create([
        'type' => AddressTypeCast::HOME
    ]);

    expect($user->addresses)->toHaveCount(1)
        ->and($user->addresses->first()->id)->toBe($address->id);
});

test('can filter addresses by type using trait methods', function () {
    $user = User::factory()->create();

    // Create different types of addresses
    Address::factory()->forUser($user)->create(['type' => AddressTypeCast::HOME]);
    Address::factory()->forUser($user)->create(['type' => AddressTypeCast::WORK]);
    Address::factory()->forUser($user)->create(['type' => AddressTypeCast::DELIVERY]);

    expect($user->homeAddress()->first())->not->toBeNull()
        ->and($user->homeAddress()->first()->type)->toBe(AddressTypeCast::HOME)
        ->and($user->workAddress()->first())->not->toBeNull()
        ->and($user->deliveryAddresses)->toHaveCount(1);
});

test('can get default address', function () {
    $user = User::factory()->create();
    $address1 = Address::factory()->forUser($user)->create(['default' => false]);
    $address2 = Address::factory()->forUser($user)->create(['default' => true]);

    expect($user->defaultAddress()->id)->toBe($address2->id);
});

test('can set default address using trait method', function () {
    $user = User::factory()->create();
    $address1 = Address::factory()->forUser($user)->create(['default' => true]);
    $address2 = Address::factory()->forUser($user)->create(['default' => false]);

    $user->setDefaultAddress($address2);

    expect($address1->fresh()->default)->toBeFalse()
        ->and($address2->fresh()->default)->toBeTrue()
        ->and($user->defaultAddress()->id)->toBe($address2->id);
});
