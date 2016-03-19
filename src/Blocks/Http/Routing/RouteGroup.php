<?php

namespace Blocks\Http\Routing;

use Blocks\Http\Request;
use Blocks\Http\Route;
use Blocks\Http\Routing\Exception\HttpNotFoundException;

class RouteGroup implements Route
{

    use MatchersTrait;
    use RoutesTrait;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function findByName($name)
    {
        foreach ($this->getRoutes() as $route) {
            $foundedRoute = $route->findByName($name);
            if (isset($foundedRoute)) {
                return $foundedRoute;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function generateUrl(Request $request)
    {
        if (!is_null($this->getParent())) {
            return $this->getParent()->generateUrl($request);
        }
        return '';
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

    public function getParameters()
    {
        return [];
    }
}
