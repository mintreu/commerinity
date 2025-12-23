<?php

declare(strict_types=1);

use App\Models\Geo\Block;
use App\Models\Geo\Country;
use App\Models\Geo\State;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('state can be created with required fields', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create([
        'name' => 'Maharashtra',
        'code' => 'MH',
    ]);

    expect($state->name)->toBe('Maharashtra')
        ->and($state->code)->toBe('MH')
        ->and($state->country_id)->toBe($country->id);
});

test('state belongs to country', function () {
    $country = Country::factory()->create(['name' => 'India']);
    $state = State::factory()->forCountry($country)->create();

    expect($state->country)->not->toBeNull()
        ->and($state->country->id)->toBe($country->id)
        ->and($state->country->name)->toBe('India');
});

test('state has blocks relationship', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();
    $block = Block::factory()->forState($state)->create();

    expect($state->blocks)->toHaveCount(1)
        ->and($state->blocks->first()->id)->toBe($block->id);
});

test('state has addresses relationship', function () {
    $country = Country::factory()->india()->create();
    $state = State::factory()->forCountry($country)->create();
    $address = \App\Models\Address::factory()->create([
        'state_code' => $state->code,
        'country_code' => $country->iso_code_2,
    ]);

    expect($state->addresses)->toHaveCount(1)
        ->and($state->addresses->first()->id)->toBe($address->id);
});

test('state byCountry scope filters by country_id', function () {
    $country1 = Country::factory()->create();
    $country2 = Country::factory()->create();

    State::factory()->forCountry($country1)->create();
    State::factory()->forCountry($country2)->create();

    $states = State::byCountry($country1->id)->get();

    expect($states)->toHaveCount(1)
        ->and($states->first()->country_id)->toBe($country1->id);
});

test('state byCountryCode scope filters by country iso_code_2', function () {
    $india = Country::factory()->india()->create();
    $other = Country::factory()->create(['iso_code_2' => 'US']);

    State::factory()->forCountry($india)->create();
    State::factory()->forCountry($other)->create();

    $states = State::byCountryCode('IN')->get();

    expect($states)->toHaveCount(1)
        ->and($states->first()->country->iso_code_2)->toBe('IN');
});

test('state code and country_id combination is unique', function () {
    $country = Country::factory()->create();
    State::factory()->forCountry($country)->create(['code' => 'MH']);

    expect(fn () => State::factory()->forCountry($country)->create(['code' => 'MH']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
