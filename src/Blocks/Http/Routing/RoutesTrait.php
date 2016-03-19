<?php

namespace Blocks\Http\Routing;

use Blocks\Http\Route;

trait RoutesTrait
{

    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * @var Route
     */
    private $parent;

    /**
     * @return Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param Route $route
     * @return $this
     */
    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
        if ($this instanceof Route) {
            $route->setParent($this);
        }
        return $this;
    }

    /**
     * @param Route[] $routes
     * @return $this
     */
    public function addRoutes(array $routes = [])
    {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }
        return $this;
    }

    /**
     * @param Route $parent
     * @return $this
     */
    public function setParent(Route $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Route
     */
    public function getParent()
    {
        return $this->parent;
    }
}
