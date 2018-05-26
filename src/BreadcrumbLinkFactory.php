<?php

namespace Fragkp\LaravelRouteBreadcrumb;

use Closure;
use TypeError;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class BreadcrumbLinkFactory
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @param \Illuminate\Http\Request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param string                    $uri
     * @param \Illuminate\Routing\Route $route
     * @return \Fragkp\LaravelRouteBreadcrumb\BreadcrumbLink|null
     */
    public function create(string $uri, Route $route)
    {
        $route = RouteParameterBinder::bind($this->request, $route);

        try {
            $resolvedTitle = static::resolveTitle(
                $route->getAction('breadcrumb'),
                static::routeParameters($route)
            );
        } catch (TypeError $error) {
            return null;
        }

        return new BreadcrumbLink($uri, $resolvedTitle);
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
