<?php

namespace Fragkp\LaravelRouteBreadcrumb;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class RouteParameterBinder
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Routing\Route $route
     * @return \Illuminate\Routing\Route
     */
    public static function bind(Request $request, Route $route)
    {
        if (is_null($compiledRoute = $route->getCompiled())) {
            return $route;
        }

        $compiledRouteParameters = $compiledRoute->getVariables();

        if (! empty($compiledRouteParameters)) {
            $currentParameters = $request->route()->parameters();

            $route->bind(new Request);

            foreach (array_only($currentParameters, $compiledRouteParameters) as $name => $parameter) {
                $route->setParameter($name, $parameter);
            }
        }

        return $route;
    }
}
