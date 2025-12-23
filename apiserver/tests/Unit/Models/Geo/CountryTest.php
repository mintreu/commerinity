<?php

declare(strict_types=1);

use App\Models\Geo\Country;
use App\Models\Geo\State;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('country can be created with required fields', function () {
    $country = Country::factory()->create([
        'name' => 'India',
        'iso_code_2' => 'IN',
        'iso_code_3' => 'IND',
    ]);

    expect($country->name)->toBe('India')
        ->and($country->iso_code_2)->toBe('IN')
        ->and($country->iso_code_3)->toBe('IND');
});

test('country has states relationship', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();

    expect($country->states)->toHaveCount(1)
        ->and($country->states->first()->id)->toBe($state->id);
});

test('country has addresses relationship', function () {
    $country = Country::factory()->india()->create();
    $state = State::factory()->forCountry($country)->create();
    $address = \App\Models\Address::factory()->create([
        'country_code' => $country->iso_code_2,
        'state_code' => $state->code,
    ]);

    expect($country->addresses)->toHaveCount(1)
        ->and($country->addresses->first()->id)->toBe($address->id);
});

test('country active scope filters active countries', function () {
    Country::factory()->active()->create(['name' => 'Active Country']);
    Country::factory()->inactive()->create(['name' => 'Inactive Country']);

    $activeCountries = Country::active()->get();

    expect($activeCountries)->toHaveCount(1)
        ->and($activeCountries->first()->name)->toBe('Active Country');
});

test('country byRegion scope filters by region', function () {
    Country::factory()->create(['region' => 'Asia']);
    Country::factory()->create(['region' => 'Europe']);

    $asianCountries = Country::byRegion('Asia')->get();

    expect($asianCountries)->toHaveCount(1)
        ->and($asianCountries->first()->region)->toBe('Asia');
});

test('country casts exchange_rate to array', function () {
    $country = Country::factory()->create([
        'exchange_rate' => ['USD' => 83.12, 'EUR' => 90.45],
    ]);

    expect($country->exchange_rate)->toBeArray()
        ->and($country->exchange_rate['USD'])->toBe(83.12);
});

test('country casts is_active to boolean', function () {
    $country = Country::factory()->create(['is_active' => 1]);

    expect($country->is_active)->toBeTrue()
        ->and($country->is_active)->toBeBool();
});

test('india factory state creates correct data', function () {
    $country = Country::factory()->india()->create();

    expect($country->name)->toBe('India')
        ->and($country->iso_code_2)->toBe('IN')
        ->and($country->iso_code_3)->toBe('IND')
        ->and($country->isd_code)->toBe(91)
        ->and($country->currency)->toBe('INR')
        ->and($country->is_active)->toBeTrue();
});
