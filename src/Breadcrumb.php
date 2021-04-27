<?php

namespace Fragkp\LaravelRouteBreadcrumb;

use RuntimeException;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;

class Breadcrumb
{
    protected Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function links(): Collection
    {
        $currentRoute = $this->router->current();

        if (is_null($currentRoute) || is_null($currentRoute->getAction('breadcrumbTitle'))) {
            return Collection::make();
        }

        if (is_null($breadcrumbParent = $currentRoute->getAction('breadcrumbParent'))) {
            return Collection::make([$this->createBreadcrumbLink($currentRoute)]);
        }

        $breadcrumbRoutes = Collection::make([$currentRoute]);

        do {
            if (is_null($parentRoute = $this->router->getRoutes()->getByName($breadcrumbParent))) {
                throw new RuntimeException("Breadcrumb parent route [$breadcrumbParent] not found.");
            }

            $breadcrumbRoutes->prepend($parentRoute);

            $breadcrumbParent = $parentRoute->getAction('breadcrumbParent');
        } while (! is_null($breadcrumbParent));

        return $breadcrumbRoutes->map(fn (Route $route) => $this->createBreadcrumbLink($route));
    }

    protected function createBreadcrumbLink(Route $route): BreadcrumbLink
    {
        return new BreadcrumbLink($route, Support::resolveBreadcrumbTitle(
            $route->getAction('breadcrumbTitle'),
            array_values(Support::resolveRouteParameters($route))
        ));
    }
}
