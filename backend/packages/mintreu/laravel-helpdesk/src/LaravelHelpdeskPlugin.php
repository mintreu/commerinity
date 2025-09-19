<?php

namespace Mintreu\LaravelHelpdesk;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource;
use Mintreu\LaravelHelpdesk\Filament\Resources\InquiryResource;

class LaravelHelpdeskPlugin implements Plugin
{
    public function getId(): string
    {
        return 'laravel-helpdesk';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
           HelpDeskResource::class,
            HelpDeskTopicResource::class,
            HelpDeskFaqResource::class,
            InquiryResource::class
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
