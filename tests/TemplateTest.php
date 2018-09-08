<?php

namespace Fragkp\LaravelRouteBreadcrumb\Tests;

use Illuminate\Support\Facades\Route;

class TemplateTest extends TestCase
{
    /** @test */
    public function it_returns_an_empty_bootstrap_3_template()
    {
        $this->get('/foo')->assertStatus(404);

        $html = (string) view('laravel-breadcrumb::bootstrap3');

        $this->assertEmpty($html);
    }

    /** @test */
    public function it_returns_the_correct_bootstrap_3_template()
    {
        Route::get('/', function (){})->breadcrumbIndex('Start');
        Route::get('/foo', function (){})->breadcrumb('First');

        $this->get('/foo')->assertStatus(200);

        $html = (string) view('laravel-breadcrumb::bootstrap3');

        $this->assertXmlStringEqualsXmlString('
        <ol class="breadcrumb">
            <li>
                <a href="http://localhost" title="Start">Start</a>
            </li>
            
            <li class="active">First</li>
        </ol>
        ', $html);
    }

    /** @test */
    public function it_returns_an_empty_bootstrap_4_template()
    {
        $this->get('/foo')->assertStatus(404);

        $html = (string) view('laravel-breadcrumb::bootstrap4');

        $this->assertEmpty($html);
    }

    /** @test */
    public function it_returns_the_correct_bootstrap_4_template()
    {
        Route::get('/', function (){})->breadcrumbIndex('Start');
        Route::get('/foo', function (){})->breadcrumb('First');

        $this->get('/foo')->assertStatus(200);

        $html = (string) view('laravel-breadcrumb::bootstrap4');

        $this->assertXmlStringEqualsXmlString('
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="http://localhost" title="Start">Start</a>
                </li>
                
                <li class="breadcrumb-item active" aria-current="page">First</li>
            </ol>
        </nav>
        ', $html);
    }

    /** @test */
    public function it_returns_an_empty_bulma_template()
    {
        $this->get('/foo')->assertStatus(404);

        $html = (string) view('laravel-breadcrumb::bulma');

        $this->assertEmpty($html);
    }

    /** @test */
    public function it_returns_the_correct_bulma_template()
    {
        Route::get('/', function (){})->breadcrumbIndex('Start');
        Route::get('/foo', function (){})->breadcrumb('First');

        $this->get('/foo')->assertStatus(200);

        $html = (string) view('laravel-breadcrumb::bulma');

        $this->assertXmlStringEqualsXmlString('
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li>
                    <a href="http://localhost" title="Start">Start</a>
                </li>
                
                <li class="is-active">
                    <a href="#" aria-current="page">First</a>
                </li>
            </ul>
        </nav>
        ', $html);
    }

    /** @test */
    public function it_returns_an_empty_foundation_6_template()
    {
        $this->get('/foo')->assertStatus(404);

        $html = (string) view('laravel-breadcrumb::foundation6');

        $this->assertEmpty($html);
    }

    /** @test */
    public function it_returns_the_correct_foundation_6_template()
    {
        Route::get('/', function (){})->breadcrumbIndex('Start');
        Route::get('/foo', function (){})->breadcrumb('First');

        $this->get('/foo')->assertStatus(200);

        $html = (string) view('laravel-breadcrumb::foundation6');

        $this->assertXmlStringEqualsXmlString('
        <nav aria-label="You are here:" role="navigation">
            <ul class="breadcrumbs">
                <li>
                    <a href="http://localhost" title="Start">Start</a>
                </li>
                
                <li>
                    <span class="show-for-sr">Current:</span> First
                    </li>
            </ul>
        </nav>
        ', $html);
    }
}
