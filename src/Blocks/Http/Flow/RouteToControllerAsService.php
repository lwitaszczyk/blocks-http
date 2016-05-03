<?php

namespace Blocks\Http\Flow;

use Blocks\Application;
use Blocks\Http\Request;

class RouteToControllerAsService extends BaseRoute
{

    /**
     * @var RouteToMethod[]
     */
    private $routesToMethod;

    /**
     * @var string
     */
    private $serviceId;

    /**
     * RouteTo constructor.
     * @param null|string $pattern
     * @param null|string $serviceId
     */
    public function __construct($pattern, $serviceId)
    {
        parent::__construct($pattern);
        $this->serviceId = $serviceId;
        $this->routesToMethod = [];
    }

    /**
     * @param RouteToMethod $routeToMethod
     * @return $this
     */
    public function addRouteToMethod(RouteToMethod $routeToMethod)
    {
        $this->routesToMethod[] = $routeToMethod;
        $routeToMethod->setParentRoute($this);
        $routeToMethod->setRouteToController($this);
        return $this;
    }

    /**
     * @param RouteToMethod[] $routesToMethod
     * @return $this
     */
    public function addRouteToMethods(array $routesToMethod = [])
    {
        foreach ($routesToMethod as $routeToMethod) {
            $this->addRouteToMethod($routeToMethod);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Request $request)
    {
        if ($this->match($request, false)) {
            foreach ($this->routesToMethod as $routeToMethod) {
                $response = $routeToMethod->process($request);
                if (!is_null($response)) {
                    return $response;
                }
            }
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function findByName($name)
    {
        foreach ($this->routesToMethod as $routeToMethod) {
            $foundedRoute = $routeToMethod->findByName($name);
            if (isset($foundedRoute)) {
                return $foundedRoute;
            }
        }
        return null;
    }

    /**
     * @return mixed
     * @throws \Blocks\DI\Exception\ServiceNotDefinedInContainerException
     */
    public function getController()
    {
        return Application::getInstance()->getContainer()->get($this->serviceId);
    }
}
