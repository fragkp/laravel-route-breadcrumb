<?php

namespace Fragkp\LaravelRouteBreadcrumb\Tests;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Fragkp\LaravelRouteBreadcrumb\Breadcrumb;
use Fragkp\LaravelRouteBreadcrumb\BreadcrumbLink;
use Illuminate\Routing\Middleware\SubstituteBindings;

class IntegrationTest extends TestCase
{
    protected static $controllerAction = 'Fragkp\\LaravelRouteBreadcrumb\\Tests\\TestController@index';

    /** @test */
    public function it_not_changes_the_default_behavior()
    {
        Route::get('/foo', static::$controllerAction);

        $this->get('/foo')->assertSuccessful();

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(0, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEmpty($breadcrumbLinks);

        $this->assertNull(app(Breadcrumb::class)->current());
        $this->assertNull(app(Breadcrumb::class)->index());
    }

    /** @test */
    public function it_is_empty_when_no_route_is_found()
    {
        $this->get('/foo')->assertStatus(404);

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(0, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEmpty($breadcrumbLinks);

        $this->assertNull(app(Breadcrumb::class)->current());
        $this->assertNull(app(Breadcrumb::class)->index());
    }

    /** @test */
    public function it_is_empty_when_an_error_occurs()
    {
        Route::get('/foo', function () {
            throw new \Exception;
        });

        $this->get('/foo')->assertStatus(500);

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(0, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEmpty($breadcrumbLinks);

        $this->assertNull(app(Breadcrumb::class)->current());
        $this->assertNull(app(Breadcrumb::class)->index());
    }

    /** @test */
    public function it_returns_always_the_breadcrumb_index()
    {
        Route::get('/', static::$controllerAction)->breadcrumbIndex('Start');

        Route::get('/foo', function () {
            throw new \Exception;
        });

        $this->get('/foo')->assertStatus(500);

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(1, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            '/'   => new BreadcrumbLink('/', 'Start'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('/', 'Start'), app(Breadcrumb::class)->index());

        $this->assertNull(app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_returns_only_the_matched_breadcrumb()
    {
        Route::get('/foo', static::$controllerAction)->breadcrumb('Foo');
        Route::get('/bar/camp', static::$controllerAction)->breadcrumb('Bar');
        Route::get('/zoo/deep/crew', static::$controllerAction)->breadcrumb('Zoo');
        Route::get('/baz', static::$controllerAction)->breadcrumb('Baz');

        $this->get('/zoo/deep/crew')->assertSuccessful();

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(1, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            'zoo/deep/crew' => new BreadcrumbLink('zoo/deep/crew', 'Zoo'),
        ]), $breadcrumbLinks);

        $this->assertNull(app(Breadcrumb::class)->index());

        $this->assertEquals(new BreadcrumbLink('zoo/deep/crew', 'Zoo'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_returns_only_the_matched_and_defined_index_breadcrumbs()
    {
        Route::get('/', static::$controllerAction)->breadcrumbIndex('Start');
        Route::get('/foo', static::$controllerAction)->breadcrumb('First');

        $this->get('/foo')->assertSuccessful();

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(2, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            '/'   => new BreadcrumbLink('/', 'Start'),
            'foo' => new BreadcrumbLink('foo', 'First'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('/', 'Start'), app(Breadcrumb::class)->index());

        $this->assertEquals(new BreadcrumbLink('foo', 'First'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_returns_always_the_first_index()
    {
        Route::get('/bar', static::$controllerAction)->breadcrumbIndex('Start first');
        Route::get('/', static::$controllerAction)->breadcrumbIndex('Start second');
        Route::get('/zoo', static::$controllerAction)->breadcrumbIndex('Start third');
        Route::get('/foo', static::$controllerAction)->breadcrumb('First');

        $this->get('/foo')->assertSuccessful();

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(2, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            'bar' => new BreadcrumbLink('bar', 'Start first'),
            'foo' => new BreadcrumbLink('foo', 'First'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('bar', 'Start first'), app(Breadcrumb::class)->index());

        $this->assertEquals(new BreadcrumbLink('foo', 'First'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_can_handle_the_breadcrumb_title_by_closure()
    {
        Route::get('/foo', static::$controllerAction)->breadcrumb(function () {
            return 'Closure title';
        });

        $this->get('/foo')->assertSuccessful();

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(1, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            'foo' => new BreadcrumbLink('foo', 'Closure title'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('foo', 'Closure title'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_can_handle_the_breadcrumb_title_by_custom_class()
    {
        Route::get('/foo', static::$controllerAction)->breadcrumb(CustomTitleResolver::class);

        $this->get('/foo')->assertSuccessful();

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(1, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            'foo' => new BreadcrumbLink('foo', 'Class title'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('foo', 'Class title'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_returns_the_breadcrumb_inside_a_group()
    {
        Route::get('/', static::$controllerAction)->breadcrumbIndex('Start');

        Route::prefix('foo')->group(function () {
            Route::get('/bar', static::$controllerAction)->breadcrumb('Inside group');
        });

        $this->get('/foo/bar')->assertSuccessful();

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(2, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            '/'       => new BreadcrumbLink('/', 'Start'),
            'foo/bar' => new BreadcrumbLink('foo/bar', 'Inside group'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('/', 'Start'), app(Breadcrumb::class)->index());

        $this->assertEquals(new BreadcrumbLink('foo/bar', 'Inside group'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_returns_the_breadcrumb_inside_a_group_with_the_group_index()
    {
        Route::get('/', static::$controllerAction)->breadcrumbIndex('Start');

        Route::prefix('foo')->group(function () {
            Route::get('/', static::$controllerAction)->breadcrumbGroup('Inside group - index');
            Route::get('/bar', static::$controllerAction)->breadcrumb('Inside group - bar');
        });

        $this->get('/foo/bar')->assertSuccessful();

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(3, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            '/'       => new BreadcrumbLink('/', 'Start'),
            'foo'     => new BreadcrumbLink('foo', 'Inside group - index'),
            'foo/bar' => new BreadcrumbLink('foo/bar', 'Inside group - bar'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('/', 'Start'), app(Breadcrumb::class)->index());

        $this->assertEquals(new BreadcrumbLink('foo/bar', 'Inside group - bar'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_can_handle_multiple_nested_groups()
    {
        Route::get('/', static::$controllerAction)->breadcrumbIndex('Start');

        Route::prefix('foo')->group(function () {
            Route::get('/', static::$controllerAction)->breadcrumbGroup('Inside group - index');
            Route::get('/bar', static::$controllerAction)->breadcrumb('Inside group - bar');

            Route::prefix('baz')->group(function () {
                Route::get('/zoo', static::$controllerAction)->breadcrumb('Inside nested group - zoo');

                Route::prefix('too')->group(function () {
                    Route::get('/', static::$controllerAction)->breadcrumbGroup('Inside nested group - group - index');
                    Route::get('/crew', static::$controllerAction)->breadcrumb('Inside nested group - group - crew');
                });
            });
        });

        $this->get('/foo/baz/too/crew')->assertSuccessful();

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(4, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            '/'                => new BreadcrumbLink('/', 'Start'),
            'foo'              => new BreadcrumbLink('foo', 'Inside group - index'),
            'foo/baz/too'      => new BreadcrumbLink('foo/baz/too', 'Inside nested group - group - index'),
            'foo/baz/too/crew' => new BreadcrumbLink('foo/baz/too/crew', 'Inside nested group - group - crew'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('/', 'Start'), app(Breadcrumb::class)->index());

        $this->assertEquals(new BreadcrumbLink('foo/baz/too/crew', 'Inside nested group - group - crew'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_can_handle_route_model_binding()
    {
        $this->migrate();

        factory(Foo::class, 1)->create();

        Route::middleware(SubstituteBindings::class)->get('/binding/{foo}', function (Foo $foo) {
            return $foo->id;
        })->breadcrumb('First');

        $this->get('/binding/1')->assertSuccessful()->assertSee('1');

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(1, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            'binding/1' => new BreadcrumbLink('binding/1', 'First'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('binding/1', 'First'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_can_handle_route_model_binding_and_resolves_title_by_closure()
    {
        $this->migrate();

        factory(Foo::class, 1)->create();

        Route::middleware(SubstituteBindings::class)->get('/binding/{foo}', function (Foo $foo) {
            return $foo->id;
        })->breadcrumb(function (Foo $foo) {
            return "Id: {$foo->id}";
        });

        $this->get('/binding/1')->assertSuccessful()->assertSee('1');

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(1, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            'binding/1' => new BreadcrumbLink('binding/1', 'Id: 1'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('binding/1', 'Id: 1'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_can_handle_route_model_binding_and_resolves_title_by_custom_class()
    {
        $this->migrate();

        factory(Foo::class, 2)->create();

        Route::middleware(SubstituteBindings::class)->get('/binding/{foo}', function (Foo $foo) {
            return $foo->id;
        })->breadcrumb(CustomRouteModelBindingTitleResolver::class);

        $this->get('/binding/2')->assertSuccessful()->assertSee('2');

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(1, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            'binding/2' => new BreadcrumbLink('binding/2', 'Id: 2'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('binding/2', 'Id: 2'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_can_handle_multiple_route_model_bindings_inside_groups()
    {
        $this->migrate();

        factory(Foo::class, 1)->create();
        factory(Bar::class, 5)->create();

        Route::get('/', static::$controllerAction)->breadcrumbIndex('Start');

        Route::prefix('first-group')->group(function () {
            Route::get('/')->breadcrumbGroup('Inside first group');

            Route::prefix('second-group')->group(function () {
                Route::get('/')->breadcrumbGroup('Inside second group');

                Route::middleware(SubstituteBindings::class)->get('/{foo}/{bar}', function (Foo $foo, Bar $bar) {
                    return $foo->id.'-'.$bar->id;
                })->breadcrumb('Binding');
            });
        });

        $this->get('/first-group/second-group/1/5')->assertSuccessful()->assertSee('1-5');

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(4, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            '/'                            => new BreadcrumbLink('/', 'Start'),
            'first-group'                  => new BreadcrumbLink('first-group', 'Inside first group'),
            'first-group/second-group'     => new BreadcrumbLink('first-group/second-group', 'Inside second group'),
            'first-group/second-group/1/5' => new BreadcrumbLink('first-group/second-group/1/5', 'Binding'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('/', 'Start'), app(Breadcrumb::class)->index());

        $this->assertEquals(new BreadcrumbLink('first-group/second-group/1/5', 'Binding'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_can_handle_custom_route_model_binding()
    {
        Route::bind('customBinding', function ($value) {
            return new CustomBinding($value);
        });

        Route::middleware(SubstituteBindings::class)->get('/binding/{customBinding}', function (CustomBinding $customBinding) {
            return $customBinding->value;
        })->breadcrumb('First');

        $this->get('/binding/foo')->assertSuccessful()->assertSee('foo');

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(1, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            'binding/foo' => new BreadcrumbLink('binding/foo', 'First'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('binding/foo', 'First'), app(Breadcrumb::class)->current());
    }

    /** @test */
    public function it_can_handle_nested_route_parameters_with_route_model_binding_for_group_index()
    {
        Route::bind('customBinding', function ($value) {
            return new CustomBinding($value);
        });

        Route::bind('secondBinding', function ($value) {
            return new SecondBinding($value);
        });

        Route::middleware(SubstituteBindings::class)->group(function () {
            Route::get('/binding/{customBinding}', function (CustomBinding $customBinding) {
                return $customBinding->value;
            })->breadcrumbGroup(function (CustomBinding $customBinding) {
                return $customBinding->value;
            });

            Route::get('/binding/{customBinding}/{secondBinding}', function (CustomBinding $customBinding, SecondBinding $secondBinding) {
                return $customBinding->value.'-'.$secondBinding->value;
            })->breadcrumb(function (CustomBinding $customBinding, SecondBinding $secondBinding) {
                return $customBinding->value.'-'.$secondBinding->value;
            });
        });

        $this->get('/binding/foo/bar')->assertSuccessful()->assertSee('foo-bar');

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(2, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            'binding/foo'     => new BreadcrumbLink('binding/foo',    'foo'),
            'binding/foo/bar' => new BreadcrumbLink('binding/foo/bar', 'foo-bar'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('binding/foo/bar', 'foo-bar'), app(Breadcrumb::class)->current());
    }
}

class TestController
{
    public function index()
    {
        return 'test';
    }
}

class Foo extends Model
{
    //
}

class Bar extends Model
{
    //
}

class CustomBinding
{
    public function __construct($value)
    {
        $this->value = $value;
    }
}

class SecondBinding
{
    public function __construct($value)
    {
        $this->value = $value;
    }
}

class CustomTitleResolver
{
    public function __invoke()
    {
        return 'Class title';
    }
}

class CustomRouteModelBindingTitleResolver
{
    public function __invoke(Foo $foo)
    {
        return "Id: {$foo->id}";
    }
}
