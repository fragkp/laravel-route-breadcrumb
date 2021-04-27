<?php

namespace Fragkp\LaravelRouteBreadcrumb\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Fragkp\LaravelRouteBreadcrumb\BreadcrumbServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            BreadcrumbServiceProvider::class,
        ];
    }
}
