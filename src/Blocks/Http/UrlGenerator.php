<?php

namespace Blocks\Http;

use Blocks\Http\Exception\HttpApplicationCanNotFoundRouteException;

class UrlGenerator
{

    /**
     * @var HttpApplication
     */
    private $application;

    /**
     * @param HttpApplication $application
     */
    public function __construct(HttpApplication $application)
    {
        $this->application = $application;
    }

    /**
     * @param $routeName
     * @param array $params
     * @return string
     * @throws HttpApplicationCanNotFoundRouteException
     */
    public function byRouteName($routeName, array $params = [])
    {
        return $this->application->urlByRouteName($routeName, $params);
    }

    /**
     * @param Route $route
     * @param array $params
     * @return string
     */
    public function byRoute(Route $route, array $params = [])
    {
        return $this->application->urlByRoute($route, $params);
    }
}
