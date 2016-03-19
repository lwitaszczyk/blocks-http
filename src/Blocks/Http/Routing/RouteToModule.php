<?php

namespace Blocks\Http\Routing;

use Blocks\Http\Request;
use Blocks\Http\Route;

class RouteToModule implements Route
{

    use MatchersTrait;
    use RoutesTrait;

    /**
     * @param string $pattern
     */
    public function __construct($pattern = null)
    {
        if (!is_null($pattern)) {
            $this->addMatcher(
                new Matcher($pattern, Matcher::MATCH_LITERAL_AT_START)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findByName($name)
    {
        foreach ($this->getRoutes() as $route) {
            $foundedRoute = $route->findByName($name);
            if (isset($foundedRoute)) {
                return $foundedRoute;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function generateUrl(Request $request)
    {
        $url = '';

        if (!is_null($this->getParent())) {
            $url = $this->getParent()->generateUrl($request);
        }

        foreach ($this->matchers as $matcher) {
            if ($matcher->matchToAttributes($request)) {
                return $url . $matcher->getPattern();
            }
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request)
    {
        $matcher = $this->match($request);
        if (!is_null($matcher)) {
            $currentPath = $request->getCurrentPath();
            $regex = '/^' . str_replace('/', '\/', $matcher->getPattern()) . '/';
            $newPath = preg_replace($regex, '', $currentPath);
            $request->setCurrentPath($newPath);
            foreach ($this->getRoutes() as $route) {
                $response = $route->process($request);
                if (isset($response)) {
                    return $response;
                }
            }
            $request->setCurrentPath($currentPath);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return [];
    }
}
