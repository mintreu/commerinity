<?php

declare(strict_types=1);

use App\Models\Geo\Block;
use App\Models\Geo\Country;
use App\Models\Geo\State;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('countries can be seeded from json', function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\Geo\\CountrySeeder'])
        ->assertSuccessful();

    expect(Country::count())->toBeGreaterThan(0);
});

test('india country is seeded with correct data', function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\Geo\\CountrySeeder'])
        ->assertSuccessful();

    $india = Country::where('iso_code_2', 'IN')->first();

    expect($india)->not->toBeNull()
        ->and($india->name)->toBe('India')
        ->and($india->iso_code_3)->toBe('IND')
        ->and($india->isd_code)->toBe(91)
        ->and($india->currency)->toBe('INR');
});

test('states can be seeded for india', function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\Geo\\CountrySeeder'])
        ->assertSuccessful();
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\Geo\\StateSeeder'])
        ->assertSuccessful();

    expect(State::count())->toBeGreaterThan(0);
});

test('blocks can be seeded for indian states', function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\Geo\\CountrySeeder'])
        ->assertSuccessful();
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\Geo\\StateSeeder'])
        ->assertSuccessful();
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\Geo\\BlockSeeder'])
        ->assertSuccessful();

    expect(Block::count())->toBeGreaterThan(0);
});

test('seeded blocks have valid state relationships', function () {
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\Geo\\CountrySeeder'])
        ->assertSuccessful();
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\Geo\\StateSeeder'])
        ->assertSuccessful();
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\Geo\\BlockSeeder'])
        ->assertSuccessful();

    $block = Block::inRandomOrder()->first();

    expect($block->state)->not->toBeNull()
        ->and($block->state->country)->not->toBeNull();
});

test('geo hierarchical relationships work correctly', function () {
    $country = Country::factory()->india()->create();
    $state = State::factory()->forCountry($country)->create();
    $block = Block::factory()->forState($state)->create();

    expect($block->state->country->id)->toBe($country->id);
});

test('multiple states can belong to same country', function () {
    $country = Country::factory()->create();
    State::factory()->forCountry($country)->count(5)->create();

    expect($country->states)->toHaveCount(5);
});

test('multiple blocks can belong to same state', function () {
    $country = Country::factory()->create();
    $state = State::factory()->forCountry($country)->create();
    Block::factory()->forState($state)->count(10)->create();

    expect($state->blocks)->toHaveCount(10);
});

test('country active status can be toggled', function () {
    $country = Country::factory()->inactive()->create();

    expect($country->is_active)->toBeFalse();

    $country->update(['is_active' => true]);

    expect($country->fresh()->is_active)->toBeTrue();
});
