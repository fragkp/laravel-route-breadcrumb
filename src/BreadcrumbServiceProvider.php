<?php

namespace Fragkp\LaravelRouteBreadcrumb;

use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class BreadcrumbServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->singleton(Breadcrumb::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        if (! Route::hasMacro('breadcrumb')) {
            Route::macro('breadcrumb', function ($title) {
                $this->action['breadcrumb'] = $title;

                return $this;
            });
        }

        if (! Route::hasMacro('breadcrumbIndex')) {
            Route::macro('breadcrumbIndex', function ($title) {
                $this->action['breadcrumbIndex'] = true;
                $this->action['breadcrumb'] = $title;

                return $this;
            });
        }

        if (! Route::hasMacro('breadcrumbGroup')) {
            Route::macro('breadcrumbGroup', function ($title) {
                $this->action['breadcrumbGroup'] = true;
                $this->action['breadcrumb'] = $title;

                return $this;
            });
        }
    }
}
