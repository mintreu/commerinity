<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use App\Models\Geo\Block;
use App\Models\Geo\State;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $state = State::inRandomOrder()->first() ?? State::factory()->create();
        $block = Block::where('state_code', $state->code)->inRandomOrder()->first()
            ?? Block::factory()->forState($state)->create();

        return [
            'title' => fake()->randomElement(['Home', 'Work', 'Other']),
            'person_name' => fake()->name(),
            'person_email' => fake()->safeEmail(),
            'person_mobile' => fake()->numerify('##########'),
            'alternate_contact' => fake()->boolean(30) ? fake()->numerify('##########') : null,
            'type' => \App\Casts\AddressTypeCast::HOME,
            'address_1' => fake()->streetAddress(),
            'address_2' => fake()->boolean(40) ? fake()->secondaryAddress() : null,
            'landmark' => fake()->boolean(50) ? fake()->randomElement([
                'Near City Hospital',
                'Opposite Main Market',
                'Behind Shopping Mall',
                'Next to School',
            ]) : null,
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'block_id' => $block->id,
            'state_code' => $state->code,
            'country_code' => $state->country->iso_code_2,
            'latitude' => fake()->boolean(70) ? fake()->latitude() : null,
            'longitude' => fake()->boolean(70) ? fake()->longitude() : null,
            'default' => false,
            'priority' => fake()->numberBetween(1, 10),
            'pickup_location' => false,
        ];
    }

    /**
     * Create address for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'addressable_type' => User::class,
            'addressable_id' => $user->id,
        ]);
    }

    /**
     * Create home address.
     */
    public function home(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'home',
            'title' => 'Home',
        ]);
    }

    /**
     * Create work address.
     */
    public function work(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => \App\Casts\AddressTypeCast::WORK,
            'title' => 'Work',
        ]);
    }

    /**
     * Create office address (alias for work).
     */
    public function office(): static
    {
        return $this->work();
    }

    /**
     * Create warehouse/hub address (standalone).
     */
    public function warehouse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => \App\Casts\AddressTypeCast::HUB,
            'title' => 'Warehouse - '.fake()->city(),
            'addressable_type' => null,
            'addressable_id' => null,
            'pickup_location' => true,
        ]);
    }

    /**
     * Create store/service point address (standalone).
     */
    public function storeAddress(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => \App\Casts\AddressTypeCast::SERVICE_POINT,
            'title' => 'Store - '.fake()->city(),
            'addressable_type' => null,
            'addressable_id' => null,
            'pickup_location' => true,
        ]);
    }

    /**
     * Create default address.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'default' => true,
        ]);
    }

    /**
     * Create address without coordinates.
     */
    public function withoutCoordinates(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    /**
     * Create address with specific coordinates.
     */
    public function withCoordinates(float $latitude, float $longitude): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    /**
     * Create address for specific block.
     */
    public function forBlock(Block $block): static
    {
        return $this->state(fn (array $attributes) => [
            'block_id' => $block->id,
            'state_code' => $block->state_code,
            'country_code' => $block->state->country->iso_code_2,
        ]);
    }
}
