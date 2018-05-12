<?php

namespace Fragkp\LaravelSimpleBreadcrumb;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;

class Breadcrumb
{
    /**
     * @var \Illuminate\Routing\Router $router
     */
    protected $router;

    /**
     * @var \Illuminate\Http\Request $request
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
        $this->router  = $router;
        $this->request = $request;
    }

    /**
     * @return \Fragkp\LaravelSimpleBreadcrumb\BreadcrumbLink|null
     */
    public function index()
    {
        $indexRoute = $this->routes()->first(function (Route $route) {
            return $route->getAction('breadcrumbIndex');
        });

        if (! $indexRoute) {
            return null;
        }

        return new BreadcrumbLink(
            $indexRoute->uri(), $indexRoute->getAction('breadcrumb')
        );
    }

    public function links()
    {
        $links = $this->groupLinks();

        if ($indexLink = $this->index()) {
            $links->prepend($indexLink, $indexLink->uri);
        }

        if ($currentLink = $this->current()) {
            $links->put($currentLink->uri, $currentLink);
        }

        return $links;
    }

    protected function groupLinks()
    {
        $groupPrefixes = $this->groupPrefixes(
            $this->request->path()
        );

        return $this->routes()
            ->filter(function (Route $route) use ($groupPrefixes) {
                return in_array($route->uri(), $groupPrefixes, true);
            })
            ->filter(function (Route $route) {
                return $route->getAction('breadcrumb') && $route->getAction('breadcrumbGroup');
            })
            ->mapWithKeys(function (Route $route) {
                return [
                    $route->uri() => new BreadcrumbLink($route->uri(), $route->getAction('breadcrumb'))
                ];
            });
    }

    /**
     * @return \Fragkp\LaravelSimpleBreadcrumb\BreadcrumbLink|null
     */
    public function current()
    {
        $route = $this->request->route();

        if (! $route) {
            return null;
        }

        $actions = $route->getAction();

        $title = $actions['breadcrumb'] ?? null;

        if (! $title) {
            return null;
        }

        return new BreadcrumbLink($this->request->path(), $title);
    }

    /**
     * @return \Illuminate\Routing\Route[]|\Illuminate\Support\Collection
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
}
