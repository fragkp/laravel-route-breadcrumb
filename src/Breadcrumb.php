<?php

namespace Fragkp\LaravelRouteBreadcrumb;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;

class Breadcrumb
{
    /**
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Illuminate\Support\Collection|\Illuminate\Routing\Route[]|null
     */
    protected $routes;

    /**
     * @param \Illuminate\Routing\Router $router
     * @param \Illuminate\Http\Request   $request
     */
    public function __construct(Router $router, Request $request)
    {
        $this->router = $router;
        $this->request = $request;
    }

    /**
     * @return \Fragkp\LaravelRouteBreadcrumb\BreadcrumbLink|null
     */
    public function index()
    {
        $indexRoute = $this->routes()->first(function (Route $route) {
            return $route->getAction('breadcrumbIndex');
        });

        if (! $indexRoute) {
            return;
        }

        return BreadcrumbLinkFactory::create($indexRoute->uri(), $indexRoute);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function links()
    {
        $links = $this->groupLinks();

        if ($indexLink = $this->index()) {
            $links->prepend($indexLink, $indexLink->uri);
        }

        if ($currentLink = $this->current()) {
            $links->put($currentLink->uri, $currentLink);
        }

        if ($indexLink && is_null($currentLink)) {
            return Collection::make([$indexLink->uri => $indexLink]);
        }

        return $links;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected function groupLinks()
    {
        $pathPrefixes = $this->groupPrefixes(
            $this->request->path()
        );

        $routeUriPrefixes = $this->groupPrefixes(
            optional($this->request->route())->uri() ?? ''
        );

        return $this->routes()
            ->filter(function (Route $route) use ($routeUriPrefixes) {
                return in_array($route->uri(), $routeUriPrefixes, true);
            })
            ->filter(function (Route $route) {
                return $route->getAction('breadcrumb') && $route->getAction('breadcrumbGroup');
            })
            ->mapWithKeys(function (Route $route) use ($routeUriPrefixes, $pathPrefixes) {
                $routeUri = $pathPrefixes[array_search($route->uri(), $routeUriPrefixes, true)];

                return [$routeUri => BreadcrumbLinkFactory::create($routeUri, $this->bindParameters($route))];
            });
    }

    /**
     * @return \Fragkp\LaravelRouteBreadcrumb\BreadcrumbLink|null
     */
    public function current()
    {
        $route = $this->request->route();

        if (! $route) {
            return;
        }

        if (! isset($route->getAction()['breadcrumb'])) {
            return;
        }

        return BreadcrumbLinkFactory::create($this->request->path(), $route);
    }

    /**
     * @return \Illuminate\Support\Collection|\Illuminate\Routing\Route[]
     */
    protected function routes()
    {
        $this->routes = $this->routes ?: Collection::make($this->router->getRoutes()->getRoutes());

        return $this->routes;
    }

    /**
     * @param string $currentPath
     * @return array
     */
    protected function groupPrefixes(string $currentPath)
    {
        $prefixes = array_map(function ($prefix) use ($currentPath) {
            return str_before($currentPath, $prefix);
        }, $currentPathParts = explode('/', $currentPath));

        $prefixes = array_filter($prefixes);

        return array_map(function ($prefix) {
            return rtrim($prefix, '/');
        }, $prefixes);
    }

    /**
     * @param \Illuminate\Routing\Route $route
     * @return \Illuminate\Routing\Route
     */
    protected function bindParameters(Route $route)
    {
        $compiledRouteParameters = $route->getCompiled()->getVariables();

        if (! empty($compiledRouteParameters)) {
            $currentParameters = $this->request->route()->parameters();

            $route->bind(new Request);

            foreach (array_only($currentParameters, $compiledRouteParameters) as $name => $parameter) {
                $route->setParameter($name, $parameter);
            }
        }

        return $route;
    }
}
