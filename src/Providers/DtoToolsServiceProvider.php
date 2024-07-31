<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Providers;

use Honeystone\DtoTools\Transformers\Contracts\MakesTransformers;
use Honeystone\DtoTools\Transformers\TransformerFactory;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

use function config;
use function dirname;

final class DtoToolsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('honeystone-dto-tools')
            ->setBasePath(dirname(__DIR__))
            ->hasConfigFile('honeystone-dto-tools');
    }

    public function packageRegistered(): void
    {
        $this->app->bind(
            MakesTransformers::class,
            static fn (): TransformerFactory => new TransformerFactory(config('honeystone-dto-tools.transformers')),
        );
    }
}
