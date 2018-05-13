<?php

namespace Fragkp\LaravelRouteBreadcrumb;

use Closure;
use Illuminate\Routing\Route;

class BreadcrumbLinkFactory
{
    /**
     * @param string                    $uri
     * @param \Illuminate\Routing\Route $route
     * @return \Fragkp\LaravelRouteBreadcrumb\BreadcrumbLink
     */
    public static function create(string $uri, Route $route)
    {
        return new BreadcrumbLink($uri, static::resolveTitle(
            $route->getAction('breadcrumb'),
            static::routeParameters($route)
        ));
    }

    /**
     * @param \Illuminate\Routing\Route $route
     * @return array
     */
    protected static function routeParameters(Route $route)
    {
        return $route->hasParameters()
            ? array_values($route->parameters())
            : [];
    }

    /**
     * @param string|\Closure $title
     * @param array           $parameters
     * @return string
     */
    protected static function resolveTitle($title, array $parameters)
    {
        if ($title instanceof Closure) {
            return $title(...$parameters);
        }

        if (class_exists($title)) {
            return app($title)(...$parameters);
        }

        return $title;
    }
}
