<?php

namespace Fragkp\LaravelSimpleBreadcrumb;

use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class BreadcrumbServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(Breadcrumb::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (! Route::hasMacro('breadcrumb')) {
            Route::macro('breadcrumb', function (string $title) {
                $this->action['breadcrumb'] = $title;

                return $this;
            });
        }

        if (! Route::hasMacro('breadcrumbIndex')) {
            Route::macro('breadcrumbIndex', function (string $title) {
                $this->action['breadcrumbIndex'] = true;
                $this->action['breadcrumb'] = $title;

                return $this;
            });
        }

        if (! Route::hasMacro('breadcrumbGroup')) {
            Route::macro('breadcrumbGroup', function (string $title) {
                $this->action['breadcrumbGroup'] = true;
                $this->action['breadcrumb'] = $title;

                return $this;
            });
        }
    }
}
