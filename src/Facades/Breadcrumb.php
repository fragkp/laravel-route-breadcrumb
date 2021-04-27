<?php

namespace Fragkp\LaravelRouteBreadcrumb\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection links()
 */
class Breadcrumb extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Fragkp\LaravelRouteBreadcrumb\Breadcrumb::class;
    }
}
