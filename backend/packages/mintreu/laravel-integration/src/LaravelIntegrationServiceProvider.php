<?php

namespace Mintreu\LaravelIntegration;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Mintreu\LaravelIntegration\Support\LaravelIntegrationRegistry;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Mintreu\LaravelIntegration\Commands\LaravelIntegrationCommand;
use Mintreu\LaravelIntegration\Testing\TestsLaravelIntegration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class LaravelIntegrationServiceProvider extends PackageServiceProvider
{
    public static string $name = 'laravel-integration';
    protected array $serviceRegistry = [];

    public static string $viewNamespace = 'laravel-integration';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasConfigFile('laravel-integration')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('mintreu/laravel-integration');
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
                    $file->getRealPath() => base_path("stubs/laravel-integration/{$file->getFilename()}"),
                ], 'laravel-integration-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsLaravelIntegration);



        // **Register dynamic providers**
        $this->serviceRegistry = LaravelIntegrationRegistry::make()->getAllProviders();


//        // Register active providers [working]
//        foreach (LaravelIntegrationRegistry::getActiveProviders() as $code) {
//
//
//            if (isset($this->serviceRegistry[$code])) {
//                $providerClass = $this->serviceRegistry[$code]['provider'];
//                // Register the service provider (calls register() + boot())
//                // only use when call from other packages
//                //$this->app->register($providerClass);
//
//                $this->app->singleton($providerClass, fn ($app) => new $providerClass(
//                    $this->serviceRegistry[$code]['key'],
//                    $this->serviceRegistry[$code]['secret'],
//                ));
//            }
//        }


        // Loop through active providers
        foreach (LaravelIntegrationRegistry::getActiveProviders() as $code) {

            if (!isset($this->serviceRegistry[$code])) {
                continue; // skip if not in registry
            }

            $providerConfig = $this->serviceRegistry[$code];
            $providerClass  = $providerConfig['provider'];

            // Unique container key, fallback to code
            $singletonKey = $providerConfig['container_key'] ?? $code;

            // Bind singleton to container
//            $this->app->singleton($singletonKey, function ($app) use ($providerClass, $providerConfig) {
//                return new $providerClass(
//                    $providerConfig['key'],
//                    $providerConfig['secret']
//                );
//            });

            $this->app->singleton($singletonKey, function ($app) use ($providerClass, $code) {
                return new $providerClass(function() use ($code) {
                    return \Mintreu\LaravelIntegration\Models\Integration::where('url', $code)->first();
                });
            });




        }



    }




    protected function getAssetPackageName(): ?string
    {
        return 'mintreu/laravel-integration';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('laravel-integration', __DIR__ . '/../resources/dist/components/laravel-integration.js'),
            //Css::make('laravel-integration-styles', __DIR__ . '/../resources/dist/laravel-integration.css'),
            //Js::make('laravel-integration-scripts', __DIR__ . '/../resources/dist/laravel-integration.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            LaravelIntegrationCommand::class,
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
            'create_laravel-integration_table',
        ];
    }
}
