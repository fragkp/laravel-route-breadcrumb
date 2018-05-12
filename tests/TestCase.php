<?php

namespace Fragkp\LaravelSimpleBreadcrumb\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Fragkp\LaravelSimpleBreadcrumb\BreadcrumbServiceProvider::class,
            \Orchestra\Database\ConsoleServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function migrate()
    {
        $this->loadMigrationsFrom(realpath(__DIR__.'/database/migrations'));

        $this->loadFactoriesUsing($this->app, realpath(__DIR__.'/database/factories'));
    }
}
