# Add breadcrumbs to your routes

[![Latest Version](https://img.shields.io/github/release/fragkp/laravel-route-breadcrumb.svg?style=flat-square)](https://github.com/fragkp/laravel-route-breadcrumb/releases)
[![Build Status](https://img.shields.io/travis/fragkp/laravel-route-breadcrumb/master.svg?style=flat-square)](https://travis-ci.org/fragkp/laravel-route-breadcrumb)
[![StyleCI](https://styleci.io/repos/133180300/shield)](https://styleci.io/repos/133180300)
[![Total Downloads](https://img.shields.io/packagist/dt/fragkp/laravel-route-breadcrumb.svg?style=flat-square)](https://packagist.org/packages/fragkp/laravel-route-breadcrumb)

This package tries to give a simple solution for breadcrumbs. Add breadcrumbs direct to your routes and display it in your views.

## Installation

You can install the package via composer:

```bash
composer require fragkp/laravel-route-breadcrumb
```

Laravel 5.5 uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
Fragkp\LaravelRouteBreadcrumb\BreadcrumbServiceProvider::class,
```

If you want also use the facade to access the main breadcrumb class, add this to your facades in app.php:

```php
'Breadcrumb' => Fragkp\LaravelRouteBreadcrumb\Facades\Breadcrumb::class,
```

## Usage

```php
TODO

Route::get('/')->breadcrumbIndex('Index');

Route::get('/foo')->breadcrumb('Foo');

Route::prefix('/bar')->function () {
    Route::get('/')->breadcrumbGroup('Bar index');

    Route::get('/zoo')->breadcrumb('Zoo');
});
```

## Testing

``` bash
./vendor/bin/phpunit
```

## ToDo
- [ ] Add ability to resolve route model binding values inside breadcrumb titles.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
