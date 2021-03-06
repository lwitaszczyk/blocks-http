<?php

namespace Blocks\Http\Flow;

use Blocks\Application;
use Blocks\Http\Request;
use Blocks\Http\Route;

class RoutesGroup extends BaseRoute
{
    /**
     * @var Route[]
     */
    private $routes;

    /**
     * RoutesGroup constructor.
     * @param null|string $pattern
     */
    public function __construct($pattern = null)
    {
        parent::__construct($pattern);
        $this->routes = [];
    }

    /**
     * @param Route $route
     * @return $this
     */
    public function addRoute(Route $route)
    {
        $this->routes[] = $route;

        if ($route instanceof BaseRoute) {
            $route->setParentRoute($this);
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
     * {@inheritDoc}
     */
    public function process(Application $application, Request $request)
    {
        if (!$this->match($application, $request, false)) {
            return null;
        }

        foreach ($this->routes as $route) {
            $response = $route->process($application, $request);
            if (!is_null($response)) {
                return $response;
            }
        }

        if (is_null($this->getParentRoute())) {
            throw new HttpNotFoundException();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function findByName($name)
    {
        foreach ($this->routes as $route) {
            $foundedRoute = $route->findByName($name);
            if (isset($foundedRoute)) {
                return $foundedRoute;
            }
        }
        return null;
    }
}
