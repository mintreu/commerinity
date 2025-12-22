<?php

namespace Mintreu\LaravelGeokit;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Mintreu\LaravelGeokit\Filament\Resources\AddressResource;
use Mintreu\LaravelGeokit\Filament\Resources\CountryResource;
use Mintreu\LaravelGeokit\Filament\Resources\StateResource;

class LaravelGeokitPlugin implements Plugin
{


    protected bool $hasAuthorResource = false;

    public function authorResource(bool $condition = true): static
    {
        // This is the setter method, where the user's preference is
        // stored in a property on the plugin object.
        $this->hasAuthorResource = $condition;

        // The plugin object is returned from the setter method to
        // allow fluent chaining of configuration options.
        return $this;
    }

    public function hasAuthorResource(Panel $panel): bool
    {
        // This is the getter method, where the user's preference
        // is retrieved from the plugin property.
        $this->hasAuthorResource = $panel->getId() == 'admin';
        return $this->hasAuthorResource;
    }





    public function getId(): string
    {
        return 'laravel-geokit';
    }

    public function register(Panel $panel): void
    {


        $panel->resources([
            CountryResource::class,
            StateResource::class,
            AddressResource::class
        ]);


    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
