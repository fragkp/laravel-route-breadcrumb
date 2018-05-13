<?php

namespace Fragkp\LaravelSimpleBreadcrumb\Tests;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Fragkp\LaravelSimpleBreadcrumb\Breadcrumb;
use Fragkp\LaravelSimpleBreadcrumb\BreadcrumbLink;
use Illuminate\Routing\Middleware\SubstituteBindings;

class IntegrationTest extends TestCase
{
    protected static $controllerAction = 'Fragkp\\LaravelSimpleBreadcrumb\\Tests\\TestController@index';

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

        $this->get('/binding/1')->assertSuccessful()->assertSee(1);

        $breadcrumbLinks = app(Breadcrumb::class)->links();

        $this->assertCount(1, $breadcrumbLinks);
        $this->assertInstanceOf(Collection::class, $breadcrumbLinks);
        $this->assertEquals(new Collection([
            'binding/1' => new BreadcrumbLink('binding/1', 'First'),
        ]), $breadcrumbLinks);

        $this->assertEquals(new BreadcrumbLink('binding/1', 'First'), app(Breadcrumb::class)->current());
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
