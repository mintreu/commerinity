<?php

namespace Mintreu\LaravelCatalogueManager;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Mintreu\LaravelCatalogueManager\Commands\LaravelCatalogueManagerCommand;
use Mintreu\LaravelCatalogueManager\Testing\TestsLaravelCatalogueManager;

class LaravelCatalogueManagerServiceProvider extends PackageServiceProvider
{
    public static string $name = 'laravel-catalogue-manager';

    public static string $viewNamespace = 'laravel-catalogue-manager';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('mintreu/laravel-catalogue-manager');
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
                    $file->getRealPath() => base_path("stubs/laravel-catalogue-manager/{$file->getFilename()}"),
                ], 'laravel-catalogue-manager-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsLaravelCatalogueManager);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'mintreu/laravel-catalogue-manager';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('laravel-catalogue-manager', __DIR__ . '/../resources/dist/components/laravel-catalogue-manager.js'),
//            Css::make('laravel-catalogue-manager-styles', __DIR__ . '/../resources/dist/laravel-catalogue-manager.css'),
//            Js::make('laravel-catalogue-manager-scripts', __DIR__ . '/../resources/dist/laravel-catalogue-manager.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            LaravelCatalogueManagerCommand::class,
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
            'create_laravel-catalogue-manager_table',
        ];
    }
}
