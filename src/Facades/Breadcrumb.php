<?php

namespace Fragkp\LaravelRouteBreadcrumb\Facades;

use Illuminate\Support\Facades\Facade;

class Breadcrumb extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Fragkp\LaravelRouteBreadcrumb\Breadcrumb::class;
    }
}
