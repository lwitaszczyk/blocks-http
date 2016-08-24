<?php

namespace Blocks\Http;

class UrlGenerator
{

    /**
     * @var Route
     */
    private $rootRoute;

    /**
     * @param Route $rootRoute
     */
    public function __construct(Route $rootRoute)
    {
        $this->rootRoute = $rootRoute;
    }

    /**
     * @param string $routeName
     * @param mixed[] $params
     * @return string
     */
    public function byRouteName($routeName, array $params = [])
    {
        $route = $this->rootRoute->findByName($routeName);
        $url = $route->generateUrl($params);
        return $url;
    }
}
