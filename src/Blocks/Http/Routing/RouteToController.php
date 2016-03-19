<?php

namespace Blocks\Http\Routing;

use Blocks\Application;
use Blocks\Http\Request;
use Blocks\Http\Route;
use Blocks\Http\Routing\Exception\HttpNotFoundException;

class RouteToController implements Route
{

    use MatchersTrait;
    use RoutesTrait;

    /**
     * @var object
     */
    private $controller;

    /**
     * @param string|object $controller
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->routes = [];
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

    /**
     * {@inheritDoc}
     */
    public function generateUrl(Request $request)
    {
        $url = '';
        if (!is_null($this->getParent())) {
            $url = $this->getParent()->generateUrl($request);
        }
        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Request $request)
    {
        if ((empty($this->matchers) || !is_null($this->match($request)))) {
            foreach ($this->getRoutes() as $route) {
                $response = $route->process($request);
                if (isset($response)) {
                    return $response;
                }
            }
        }
        if (is_null($this->getParent())) {
            throw new HttpNotFoundException();
        } else {
            return null;
        }
    }

    /**
     * @return object
     */
    public function getControllerInstance()
    {
        if (is_string($this->controller)) {
            $container = Application::getInstance()->getContainer();
            return $container->get($this->controller);
        } elseif (is_object($this->controller)) {
            return $this->controller;
        } else {
            return $this->controller;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters()
    {
        return [];
    }
}
