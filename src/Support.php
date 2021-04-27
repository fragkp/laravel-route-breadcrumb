<?php

namespace Fragkp\LaravelRouteBreadcrumb;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Routing\Route;

class Support
{
    public static function resolveBreadcrumbTitle($title, array $parameters): string
    {
        if ($title instanceof Closure) {
            return $title(...$parameters);
        }

        if (is_string($title) && class_exists($title)) {
            return app($title)(...$parameters);
        }

        if (is_callable($title)) {
            return $title(...$parameters);
        }

        return $title;
    }

    public static function resolveRouteParameters(Route $route): array
    {
        if (! $route->hasParameters() || is_null($route->getCompiled())) {
            $route = $route->bind(request());
        }

        return Arr::only(
            request()->route()->parameters(),
            $route->getCompiled()->getVariables()
        );
    }
}
