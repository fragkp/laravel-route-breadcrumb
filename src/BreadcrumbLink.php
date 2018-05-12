<?php

namespace Fragkp\LaravelSimpleBreadcrumb;

class BreadcrumbLink
{
    /**
     * @var string $uri
     */
    public $uri;

    /**
     * @var string $title
     */
    public $title;

    /**
     * @param string $uri
     * @param string $title
     */
    public function __construct(string $uri, string $title)
    {
        $this->uri = $uri;
        $this->title = $title;
    }
}
