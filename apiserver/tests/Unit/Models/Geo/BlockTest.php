<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Geo\Block;
use App\Models\Geo\Country;
use App\Models\Geo\State;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('block can be created with required fields', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();
    $block = Block::factory()->forState($state)->create([
        'name' => 'Mumbai',
        'district_name' => 'Mumbai',
    ]);

    expect($block->name)->toBe('Mumbai')
        ->and($block->district_name)->toBe('Mumbai')
        ->and($block->state_code)->toBe($state->code);
});

test('block belongs to state', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create(['name' => 'Maharashtra']);
    $block = Block::factory()->forState($state)->create();

    expect($block->state)->not->toBeNull()
        ->and($block->state->id)->toBe($state->id)
        ->and($block->state->name)->toBe('Maharashtra');
});

test('block has addresses relationship', function () {
    $country = Country::factory()->india()->create();
    $state = State::factory()->forCountry($country)->create();
    $block = Block::factory()->forState($state)->create();
    $address = Address::factory()->forBlock($block)->create();

    expect($block->addresses)->toHaveCount(1)
        ->and($block->addresses->first()->id)->toBe($address->id);
});

test('block byState scope filters by state_code', function () {
    $country = Country::factory()->create();
    $state1 = State::factory()->forCountry($country)->create(['code' => 'MH']);
    $state2 = State::factory()->forCountry($country)->create(['code' => 'DL']);

    Block::factory()->forState($state1)->create();
    Block::factory()->forState($state2)->create();

    $blocks = Block::byState('MH')->get();

    expect($blocks)->toHaveCount(1)
        ->and($blocks->first()->state_code)->toBe('MH');
});

test('block byDistrict scope filters by district_name', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();

    Block::factory()->forState($state)->create(['district_name' => 'Mumbai']);
    Block::factory()->forState($state)->create(['district_name' => 'Pune']);

    $blocks = Block::byDistrict('Mumbai')->get();

    expect($blocks)->toHaveCount(1)
        ->and($blocks->first()->district_name)->toBe('Mumbai');
});

test('block hasCoordinates returns true when both latitude and longitude are set', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();
    $block = Block::factory()->forState($state)->withCoordinates(19.0760, 72.8777)->create();

    expect($block->hasCoordinates())->toBeTrue();
});

test('block hasCoordinates returns false when coordinates are null', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();
    $block = Block::factory()->forState($state)->withoutCoordinates()->create();

    expect($block->hasCoordinates())->toBeFalse();
});

test('block coordinates are cast to decimal', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();
    $block = Block::factory()->forState($state)->create([
        'latitude' => 19.0760,
        'longitude' => 72.8777,
    ]);

    expect((string) $block->latitude)->toBe('19.07600000')
        ->and((string) $block->longitude)->toBe('72.87770000');
});
