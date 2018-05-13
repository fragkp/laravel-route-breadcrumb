<?php

use Fragkp\LaravelRouteBreadcrumb\Tests\Bar;
use Fragkp\LaravelRouteBreadcrumb\Tests\Foo;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Foo::class, function () {
    return [];
});

$factory->define(Bar::class, function () {
    return [];
});
