<?php

namespace Blocks\Http\Flow;

use Blocks\Application;
use Blocks\AttributesTrait;
use Blocks\Http\RouteMatcher;
use Blocks\Http\Request;
use Blocks\Http\Route;

abstract class BaseRoute implements Route
{
    use AttributesTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var Route
     */
    private $parentRoute;

    /**
     * RouteToService constructor.
     * @param string $name
     * @param string $pattern
     */
    public function __construct($pattern = null, $name = null)
    {
        $this->name = $name;
        $this->pattern = $pattern;
    }

    /**
     * @param Application $application
     * @param Request $request
     * @param bool $exact
     * @return bool
     * @throws \Exception
     */
    public function match(Application $application, Request $request, $exact = true)
    {
        $exact = (bool)$exact;

        foreach ($this->attributes as $attributeName => $attributeValue) {
            $requestParameterValue = $request->getAttribute($attributeName);
            if (!is_null($requestParameterValue) && ($requestParameterValue !== $attributeValue)) {
                return false;
            }
        }

        if (is_null($this->pattern)) {
            return true;
        }

        $matcher = $application->getContainer()->get(RouteMatcher::class);
        return $matcher->matchRequestParameters($this, $request, $exact);
    }

    /**
     * @param Application $application
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public function generateUrl(Application $application, array $params = [])
    {
        $matcher = $application->getContainer()->get(RouteMatcher::class);
        return $matcher->generateUrl($this, $params);
    }

    /**
     * @return string
     */
    public function getAbsolutePattern()
    {
        $absolutePattern = '';

        $parentRoute = $this->getParentRoute();
        if (isset($parentRoute)) {
            $absolutePattern = $parentRoute->getAbsolutePattern();
        }

        $absolutePattern .= $this->getPattern();
        return $absolutePattern;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return Route
     */
    public function getParentRoute()
    {
        return $this->parentRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function setParentRoute(Route $parentRoute)
    {
        $this->parentRoute = $parentRoute;
        return $this;
    }
}
