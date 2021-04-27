<?php

namespace Fragkp\LaravelRouteBreadcrumb;

use Illuminate\Routing\Route;

class BreadcrumbLink
{
    protected Route $route;

    protected string $title;

    public function __construct(Route $route, string $title)
    {
        $this->route = $route;
        $this->title = $title;
    }

    public function route(): Route
    {
        return $this->route;
    }

    public function title(): string
    {
        return $this->title;
    }
}
