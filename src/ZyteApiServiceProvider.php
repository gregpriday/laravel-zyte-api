<?php

namespace GregPriday\ZyteApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use GregPriday\ZyteApi\Commands\ZyteApiCommand;

class ZyteApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-zyte-api')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-zyte-api_table')
            ->hasCommand(ZyteApiCommand::class);
    }
}
