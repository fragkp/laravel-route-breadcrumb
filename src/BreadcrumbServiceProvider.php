<?php

namespace Fragkp\LaravelRouteBreadcrumb;

use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class BreadcrumbServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Breadcrumb::class);

        if (! Route::hasMacro('breadcrumb')) {
            Route::macro('breadcrumb', function ($title, $parent = null) {
                $this->action['breadcrumbTitle'] = $title;
                $this->action['breadcrumbParent'] = $parent;

                return $this;
            });
        }
    }
}
