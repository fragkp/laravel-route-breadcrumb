<?php

namespace Fragkp\LaravelRouteBreadcrumb\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            \Fragkp\LaravelRouteBreadcrumb\BreadcrumbServiceProvider::class,
            \Orchestra\Database\ConsoleServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function migrate(): void
    {
        $this->loadMigrationsFrom(realpath(__DIR__.'/database/migrations'));

        $this->withFactories(realpath(__DIR__.'/database/factories'));
    }
}
