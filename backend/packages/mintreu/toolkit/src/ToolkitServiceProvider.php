<?php

namespace Mintreu\Toolkit;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Mintreu\Toolkit\Commands\ToolkitCommand;

class ToolkitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('toolkit')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(ToolkitCommand::class);
    }
}
