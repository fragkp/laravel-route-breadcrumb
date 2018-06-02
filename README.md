# Add breadcrumbs to your routes

[![Latest Version](https://img.shields.io/github/release/fragkp/laravel-route-breadcrumb.svg?style=flat-square)](https://github.com/fragkp/laravel-route-breadcrumb/releases)
[![Build Status](https://img.shields.io/travis/fragkp/laravel-route-breadcrumb/master.svg?style=flat-square)](https://travis-ci.org/fragkp/laravel-route-breadcrumb)
[![StyleCI](https://styleci.io/repos/133180300/shield)](https://styleci.io/repos/133180300)
[![Total Downloads](https://img.shields.io/packagist/dt/fragkp/laravel-route-breadcrumb.svg?style=flat-square)](https://packagist.org/packages/fragkp/laravel-route-breadcrumb)

This package tries to give a simple solution for breadcrumbs. Add breadcrumbs direct to your routes and display them in your views.

## Installation

You can install the package via composer:

```bash
composer require fragkp/laravel-route-breadcrumb
```

Laravel 5.5 uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

If you don't use Auto-Discovery, add the ServiceProvider to the providers array in config/app.php

```php
Fragkp\LaravelRouteBreadcrumb\BreadcrumbServiceProvider::class,
```

If you want also use the facade to access the main breadcrumb class, add this line to your facades array in config/app.php:

```php
'Breadcrumb' => Fragkp\LaravelRouteBreadcrumb\Facades\Breadcrumb::class,
```

## Usage

### Defining breadcrumbs

#### Basic

To add a breadcrumb title to your route, call the `breadcrumb` method and pass your title. 
```php
Route::get('/')->breadcrumb('Your custom title');
```

#### Index

On some websites, you wish to have always an index inside your breadcrumbs. Use the `breadcrumbIndex` method.
**This method should only be used once.**
> Note: `breadcrumbIndex` sets also the breadcrumb title for this route.
```php
Route::get('/')->breadcrumbIndex('Start');

Route::get('/foo')->breadcrumb('Your custom title');
```

#### Inside groups

The `breadcrumb` method will also work inside route groups.
```php
Route::get('/')->breadcrumbIndex('Start');

Route::prefix('/foo')->group(function () {
    Route::get('/bar')->breadcrumb('Your custom title');
});
```

#### Group index

Also, it is possible to specify a group index title by calling `breadcrumbGroup`.
**This method should only be used once inside a group.**
> Note: `breadcrumbGroup` sets also the breadcrumb title for this route.
```php
Route::get('/')->breadcrumbIndex('Start');

Route::prefix('/foo')->group(function () {
    Route::get('/')->breadcrumbGroup('Foo group index');

    Route::get('/bar')->breadcrumb('Your custom title');
});
```

#### Custom title resolver

If you want to customize your breadcrumb title, you could pass a closure or callback to all breadcrumb methods.
```php
Route::get('/')->breadcrumb(function () {
    return 'Your custom title';
});
```

You could also pass a fully qualified class name. This will invoke your class.
```php
Route::get('/')->breadcrumb(YourCustomTitleResolver::class);

class YourCustomTitleResolver
{
    public function __invoke()
    {
        return 'Your custom title';
    }
}
```

##### Route parameters

All route parameters will be passed to your resolver. Route model binding is also supported.
```php
Route::get('/{foo}')->breadcrumb(YourCustomTitleResolver::class);

class YourCustomTitleResolver
{
    public function __invoke(Foo $foo)
    {
        return "Title: {$foo->title}";
    }
}
```

### Accessing breadcrumb

#### Links

The `links` method will return a `Collection` of `BreadcrumbLink`.
> Note: The array is indexed by the uri.
```php
app(Breadcrumb::class)->links(); // or use here the facade
```
Example result:
```php
Illuminate\Support\Collection {#266
    #items: array:2 [
        "/" => Fragkp\LaravelRouteBreadcrumb\BreadcrumbLink {#41
            +uri: "/"
            +title: "Start"
        }
        "foo" => Fragkp\LaravelRouteBreadcrumb\BreadcrumbLink {#262
            +uri: "foo"
            +title: "Your custom title"
        }
    ]
}
```

#### Index

The `index` method will return a single instance of `BreadcrumbLink`. If you haven't defined any index, null is returned.
```php
app(Breadcrumb::class)->index(); // or use here the facade
```
Example result:
```php
Fragkp\LaravelRouteBreadcrumb\BreadcrumbLink {#36
    +uri: "/"
    +title: "Start"
}
```

#### Current

The `current` method will return a single instance of `BreadcrumbLink`. If no route is provided (e.g. on errors), null is returned.
```php
app(Breadcrumb::class)->current(); // or use here the facade
```
Example result:
```php
Fragkp\LaravelRouteBreadcrumb\BreadcrumbLink {#36
    +uri: "/"
    +title: "Your custom title"
}
```

#### View example

A good way to access the breadcrumb inside your views is to bound it via a View Composer.
> For more information about View Composers, have a look at the [Laravel docs](https://laravel.com/docs/5.6/views#view-composers).
```php
// app/Providers/AppServiceProvider.php

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('your-view', function ($view) {
            $view->with('breadcrumb', app(Breadcrumb::class)->links());
        });
    }
}
```
```php
// resources/views/breadcrumb.blade.php

<ul>
    @foreach ($breadcrumb as $link)
        <li>
            <a href="{{ url($link->uri) }}">{{ $link->title }}</a>
        </li>
    @endforeach
</ul>
```

## Testing

``` bash
./vendor/bin/phpunit
```

## License

MIT License (MIT). Please see [License File](LICENSE.md) for more information.
