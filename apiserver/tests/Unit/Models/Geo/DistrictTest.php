<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Geo\Block;
use App\Models\Geo\Country;
use App\Models\Geo\District;
use App\Models\Geo\State;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('district belongs to state', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();
    $district = District::factory()->forState($state)->create();

    expect($district->state)->not->toBeNull()
        ->and($district->state->id)->toBe($state->id);
});

test('district has blocks relationship', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();
    $district = District::factory()->forState($state)->create();
    $block = Block::factory()->forState($state)->create([
        'district_id' => $district->id,
        'district_name' => $district->name,
    ]);

    expect($district->blocks)->toHaveCount(1)
        ->and($district->blocks->first()->id)->toBe($block->id);
});

test('district has addresses relationship', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();
    $district = District::factory()->forState($state)->create();
    $block = Block::factory()->forState($state)->create([
        'district_id' => $district->id,
        'district_name' => $district->name,
    ]);
    $address = Address::factory()->forBlock($block)->create([
        'district_id' => $district->id,
    ]);

    expect($district->addresses)->toHaveCount(1)
        ->and($district->addresses->first()->id)->toBe($address->id);
});

