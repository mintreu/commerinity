<?php

namespace Mintreu\LaravelGeokit\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mintreu\LaravelGeokit\Casts\AddressTypeCast;
use Mintreu\LaravelGeokit\Models\Address;
use Mintreu\LaravelGeokit\Models\State;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition()
    {
        $state = State::inRandomOrder()->with('blocks')->first();

        $block = null;
        if ($state && $state->blocks->isNotEmpty()) {
            $block = $state->blocks->random();
        }

        $type = collect(AddressTypeCast::cases())
            ->pluck('value')
            ->random();

        return [
            'pickup_location'   => fake()->optional()->address(),
            'person_name'       => fake()->word() . ' Address',
            'person_mobile'     => fake()->numerify('##########'),
            'alternate_contact' => '',
            'type'              => $type,
            'address_1'         => fake()->address(),
            'village'           => 'Line Two',
            'landmark'          => '',
            'city'              => fake()->randomElement(['Delhi', 'Kolkata', 'Mumbai']),
            'postal_code'       => 700055,
            'state_code'        => $state?->code, // avoid null error if no states exist
            'block_id'          => $block?->id,
            'default'           => $type === AddressTypeCast::HOME->value,
            'priority'          => 1,
            'country_code'      => config('laravel-geokit.default_country'),
        ];
    }
}
