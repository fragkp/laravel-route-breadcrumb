<?php

namespace Fragkp\LaravelRouteBreadcrumb;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\View;
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
        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'laravel-breadcrumb');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/laravel-breadcrumb'),
        ], 'views');

        $this->app->singleton(Breadcrumb::class);

        View::composer('laravel-breadcrumb::*', function (\Illuminate\View\View $view) {
            $view->with('breadcrumb', $this->app->make(Breadcrumb::class)->links());
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
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

        if (! Route::hasMacro('breadcrumbCollection')) {
            Route::macro('breadcrumbCollection', function (string $collection) {
                $this->action['breadcrumbCollection'] = $collection;

                return $this;
            });
        }
    }
}
