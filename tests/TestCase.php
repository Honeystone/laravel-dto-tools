<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests;

use Honeystone\DtoTools\Providers\DtoToolsServiceProvider;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * @return array<class-string<ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [DtoToolsServiceProvider::class];
    }
}
