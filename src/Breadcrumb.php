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

        return app(BreadcrumbLinkFactory::class)->create($indexRoute->uri(), $indexRoute);
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
        $pathPrefixes = $this->groupPrefixes($this->request->path());

        return $this->routes()
            ->filter(function (Route $route) {
                return $route->getAction('breadcrumb') && $route->getAction('breadcrumbGroup');
            })
            ->mapWithKeys(function (Route $route) use ($pathPrefixes) {
                $routeUri = $pathPrefixes[substr_count($route->uri(), '/') + 1];

                return [$routeUri => app(BreadcrumbLinkFactory::class)->create($routeUri, $route)];
            });
    }

    /**
     * @return \Fragkp\LaravelRouteBreadcrumb\BreadcrumbLink|null
     */
    public function current()
    {
        $route = $this->request->route();

        if (! $route || ! isset($route->getAction()['breadcrumb'])) {
            return;
        }

        return app(BreadcrumbLinkFactory::class)->create($this->request->path(), $route);
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
        $prefixes = explode('/', $currentPath);

        $prefixes = array_map(function ($prefix) use ($prefixes) {
            $startPrefixes = implode('/', array_slice($prefixes, 0, array_search($prefix, $prefixes)));

            return ltrim("{$startPrefixes}/{$prefix}", '/');
        }, $prefixes);

        $prefixes = array_filter($prefixes);

        return array_prepend($prefixes, '/');
    }
}
