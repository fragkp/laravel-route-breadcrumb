<?php

namespace Fragkp\LaravelRouteBreadcrumb;

class BreadcrumbLink
{
    /**
     * @var string
     */
    public $uri;

    /**
     * @var string
     */
    public $title;

    /**
     * @param string $uri
     * @param string $title
     */
    public function __construct(string $uri, string $title)
    {
        $this->uri   = $uri;
        $this->title = $title;
    }
}
