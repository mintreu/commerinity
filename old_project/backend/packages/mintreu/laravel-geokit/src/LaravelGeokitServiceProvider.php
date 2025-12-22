<?php

namespace Mintreu\LaravelGeokit;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Mintreu\LaravelGeokit\Commands\LaravelGeokitSeedingCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Mintreu\LaravelGeokit\Commands\LaravelGeokitInstallCommand;
use Mintreu\LaravelGeokit\Testing\TestsLaravelGeokit;

class LaravelGeokitServiceProvider extends PackageServiceProvider
{
    public static string $name = 'laravel-geokit';

    public static string $viewNamespace = 'laravel-geokit';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasConfigFile('laravel-geokit')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('mintreu/laravel-geokit');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/laravel-geokit/{$file->getFilename()}"),
                ], 'laravel-geokit-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsLaravelGeokit);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'mintreu/laravel-geokit';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('laravel-geokit', __DIR__ . '/../resources/dist/components/laravel-geokit.js'),
           // Css::make('laravel-geokit-styles', __DIR__ . '/../resources/dist/laravel-geokit.css'),
           // Js::make('laravel-geokit-scripts', __DIR__ . '/../resources/dist/laravel-geokit.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            LaravelGeokitInstallCommand::class,
            LaravelGeokitSeedingCommand::class
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_countries_table',
            'create_states_table',
            'create_blocks_table',
            'create_address_table',
        ];
    }
}
