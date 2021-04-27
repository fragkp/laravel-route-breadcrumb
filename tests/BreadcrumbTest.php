<?php

use function Pest\Laravel\get;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Fragkp\LaravelRouteBreadcrumb\Breadcrumb;
use Illuminate\Routing\Middleware\SubstituteBindings;

it('not changes the default behavior', function () {
    Route::get('/foo', fn () => 'test');

    get('/foo')->assertSuccessful();

    expect(app(Breadcrumb::class)->links())
        ->toHaveCount(0)
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('is empty when no route is found', function () {
    get('/foo')->assertStatus(404);

    expect(app(Breadcrumb::class)->links())
        ->toHaveCount(0)
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('is empty when an error occurs', function () {
    Route::get('/', function () {
        throw new Exception;
    });

    get('/')->assertStatus(500);

    expect(app(Breadcrumb::class)->links())
        ->toHaveCount(0)
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('returns the current breadcrumb link', function () {
    Route::get('/', fn () => 'test')->name('index')->breadcrumb('Index Breadcrumb');

    get('/')->assertOk();

    expect($breadcrumbLinks = app(Breadcrumb::class)->links())
        ->toHaveCount(1)
        ->toBeInstanceOf(Collection::class)
        ->and($breadcrumbLinks->map->title()->toArray())
        ->toBe([
            'Index Breadcrumb',
        ]);
});

it('returns the parent breadcrumb links', function () {
    Route::get('/', fn () => 'test')->name('index')->breadcrumb('Index Breadcrumb');
    Route::get('/foo', fn () => 'test')->name('foo')->breadcrumb('Foo Breadcrumb', 'index');
    Route::get('/foo/bar', fn () => 'test')->name('foo.bar')->breadcrumb('Foo Bar Breadcrumb', 'foo');

    get('/foo/bar')->assertOk();

    expect($breadcrumbLinks = app(Breadcrumb::class)->links())
        ->toHaveCount(3)
        ->toBeInstanceOf(Collection::class)
        ->and($breadcrumbLinks->map->title()->toArray())
        ->toBe([
            'Index Breadcrumb',
            'Foo Breadcrumb',
            'Foo Bar Breadcrumb',
        ]);
});

it('throws an exception when parent not found', function () {
    Route::get('/', fn () => 'test')->name('index')->breadcrumb('Index Breadcrumb');
    Route::get('/foo', fn () => 'test')->name('not-found')->breadcrumb('Foo Breadcrumb', 'index');
    Route::get('/foo/bar', fn () => 'test')->name('foo.bar')->breadcrumb('Foo Bar Breadcrumb', 'foo');

    get('/foo/bar')->assertOk();

    app(Breadcrumb::class)->links();
})->throws(RuntimeException::class, 'Breadcrumb parent route [foo] not found.');

it('can handle the breadcrumb title by closure', function () {
    Route::get('/', fn () => 'test')->name('index')->breadcrumb(fn () => 'Index Breadcrumb');

    get('/')->assertOk();

    expect($breadcrumbLinks = app(Breadcrumb::class)->links())
        ->toHaveCount(1)
        ->toBeInstanceOf(Collection::class)
        ->and($breadcrumbLinks->map->title()->toArray())
        ->toBe([
            'Index Breadcrumb',
        ]);
});

it('can handle the breadcrumb title by callable', function () {
    $resolver = new class {
        public function getTitle(): string
        {
            return 'Index Breadcrumb';
        }
    };

    Route::get('/', fn () => 'test')->name('index')->breadcrumb([$resolver, 'getTitle']);

    get('/')->assertOk();

    expect($breadcrumbLinks = app(Breadcrumb::class)->links())
        ->toHaveCount(1)
        ->toBeInstanceOf(Collection::class)
        ->and($breadcrumbLinks->map->title()->toArray())
        ->toBe([
            'Index Breadcrumb',
        ]);
});

it('can handle the breadcrumb title by callable with parameters', function () {
    $resolver = new class {
        public function getTitle(string $foo, string $bar): string
        {
            return "$foo - $bar";
        }
    };

    Route::get('/{foo}/{bar}', fn () => 'test')->name('index')->breadcrumb([$resolver, 'getTitle']);

    get('/foo/bar')->assertOk();

    expect($breadcrumbLinks = app(Breadcrumb::class)->links())
        ->toHaveCount(1)
        ->toBeInstanceOf(Collection::class)
        ->and($breadcrumbLinks->map->title()->toArray())
        ->toBe([
            'foo - bar',
        ]);
});

it('can handle the breadcrumb title by custom class', function () {
    $resolver = new class {
        public function __invoke(string $foo, string $bar): string
        {
            return "$foo - $bar";
        }
    };

    Route::get('/{foo}/{bar}', fn () => 'test')->name('index')->breadcrumb(get_class($resolver));

    get('/foo/bar')->assertOk();

    expect($breadcrumbLinks = app(Breadcrumb::class)->links())
        ->toHaveCount(1)
        ->toBeInstanceOf(Collection::class)
        ->and($breadcrumbLinks->map->title()->toArray())
        ->toBe([
            'foo - bar',
        ]);
});

it('can handle route model binding', function () {
    Route::bind('customBinding', fn ($id) => "custom-binding-$id");

    Route::get('/binding/{customBinding}', fn ($customBinding) => $customBinding)->name('index')->breadcrumb(fn ($customBinding) => $customBinding)->middleware(SubstituteBindings::class);

    get('/binding/5')->assertOk()->assertSee('custom-binding-5');

    expect($breadcrumbLinks = app(Breadcrumb::class)->links())
        ->toHaveCount(1)
        ->toBeInstanceOf(Collection::class)
        ->and($breadcrumbLinks->map->title()->toArray())
        ->toBe([
            'custom-binding-5',
        ]);
});
