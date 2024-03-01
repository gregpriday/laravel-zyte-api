<?php

namespace GregPriday\ZyteApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasConfigFile();
    }

    public function packageBooted()
    {
        $this->app->singleton(ZyteApi::class, function () {
            return new ZyteApi(config('zyte-api.key'));
        });
    }
}
