<?php

namespace Blocks\Http\Flow;

use Blocks\Http\Request;
use Blocks\Http\Route;
use Blocks\Http\Routing\Exception\HttpNotFoundException;

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

    public function beforeProcess(Request $request) {

    }

    /**
     * {@inheritDoc}
     */
    public function process(Request $request)
    {
        if (!$this->match($request, false)) {
            return null;
        }

        $this->beforeProcess($request);

        foreach ($this->routes as $route) {
            $response = $route->process($request);
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
