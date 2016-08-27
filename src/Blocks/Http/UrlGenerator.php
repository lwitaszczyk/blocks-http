<?php

namespace Blocks\Http;

use Blocks\Application;

class UrlGenerator
{

    /**
     * @var Route
     */
    private $rootRoute;

    /**
     * @var Application
     */
    private $application;

    /**
     * UrlGenerator constructor.
     * @param Application $application
     * @param Route $rootRoute
     */
    public function __construct(Application $application, Route $rootRoute)
    {
        $this->rootRoute = $rootRoute;
        $this->application = $application;
    }

    /**
     * @param string $routeName
     * @param mixed[] $params
     * @return string
     */
    public function byRouteName($routeName, array $params = [])
    {
        $route = $this->rootRoute->findByName($routeName);
        $url = $route->generateUrl($this->application, $params);
        return $url;
    }
}
