# Add breadcrumbs to your routes

[![Latest Version](https://img.shields.io/packagist/v/fragkp/laravel-route-breadcrumb.svg?style=flat-square)](https://github.com/fragkp/laravel-route-breadcrumb/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/fragkp/laravel-route-breadcrumb.svg?style=flat-square)](https://packagist.org/packages/fragkp/laravel-route-breadcrumb)

This package tries to give a simple solution for breadcrumbs.

## Installation

You can install the package via composer:

```bash
composer require fragkp/laravel-route-breadcrumb
```

If you want also use the facade to access the main breadcrumb class, use the mentioned class below or add this line to your facades array in config/app.php:

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

#### Defining parents

Use the second parameter to set the route name of its parent.
```php
Route::get('/')->name('index')->breadcrumb('Index');
Route::get('/foo')->name('foo')->breadcrumb('Foo', 'index');
```

This will define `index` as the parent of `foo`.

#### Custom title resolver

If you want to customize your breadcrumb title, you could pass a closure to all breadcrumb methods.
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

You may also pass a callable.
```php
Route::get('/foo/{id}')->breadcrumb([app('my_breadcrumb_resolver'), 'resolve']);

// my_breadcrumb_resolver
class MyBreadcrumbResolver
{
    public function resolve($id)
    {
        $title = $this->repo->findById($id);
        
        return $title->getName();
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

### Accessing breadcrumb links

The `links` method will return a `Collection` of `BreadcrumbLink`.
```php
app(\Fragkp\LaravelRouteBreadcrumb\Breadcrumb::class)->links();
// or
\Fragkp\LaravelRouteBreadcrumb\Facades\Breadcrumb::links();
```

Example result:
```php
Illuminate\Support\Collection {
    #items: array:2 [
        Fragkp\LaravelRouteBreadcrumb\BreadcrumbLink {
            +title: "Start"
            +route: // Instance of Illuminate\Routing\Route
        }
        Fragkp\LaravelRouteBreadcrumb\BreadcrumbLink {
            +title: "Your custom title"
            +route: // Instance of Illuminate\Routing\Route
        }
    ]
}
```

#### View example

A good way to access the breadcrumb inside your views is to bound it via a View Composer.
> For more information about View Composers, have a look at the [Laravel docs](https://laravel.com/docs/master/views#view-composers).
```php
class YourServiceProvider extends ServiceProvider
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
<ul>
    @foreach ($breadcrumb as $link)
        <li>
            <a href="{{ url($link->uri()) }}">{{ $link->title }}</a>
        </li>
    @endforeach
</ul>
```

## Testing

``` bash
composer test
```

## License

MIT License (MIT). Please see [License File](LICENSE.md) for more information.
