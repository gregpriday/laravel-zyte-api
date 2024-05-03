<?php

namespace GregPriday\ZyteApi;

use GregPriday\ZyteApi\Proxy\ZyteClient;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ZyteApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-zyte-api')
            ->hasConfigFile('zyte');
    }

    public function packageBooted()
    {
        $this->app->singleton(ZyteApi::class, function () {
            return new ZyteApi(
                config('zyte.api.key'),
                concurrency: config('zyte.api.concurrency')
            );
        });

        $this->app->singleton(ZyteClient::class, function () {
            return new ZyteClient(config('zyte.proxy'));
        });
    }
}
