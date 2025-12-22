<?php

namespace Mintreu\LaravelTransaction;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Mintreu\LaravelTransaction\Filament\Resources\BeneficiaryAccountResource;
use Mintreu\LaravelTransaction\Filament\Resources\TransactionResource;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource;

class LaravelTransactionPlugin implements Plugin
{
    public function getId(): string
    {
        return 'laravel-transaction';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
           WalletResource::class,
           TransactionResource::class,
            BeneficiaryAccountResource::class
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
