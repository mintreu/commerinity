<?php

namespace Mintreu\LaravelNaukriManager;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource;

class LaravelNaukriManagerPlugin implements Plugin
{
    public function getId(): string
    {
        return 'laravel-naukri-manager';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            NaukriApplicationResource::class,
            NaukriResource::class
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
