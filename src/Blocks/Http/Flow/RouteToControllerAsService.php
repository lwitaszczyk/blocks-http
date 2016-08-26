<?php

namespace Blocks\Http\Flow;

use Blocks\Application;
use Blocks\Http\Request;
use Blocks\Invoker;

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
     * @param string $serviceId
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
//        $routeToMethod->setRouteToController($this);
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
    public function process(Application $application, Request $request)
    {
        if ($this->match($request, false)) {
            foreach ($this->routesToMethod as $routeToMethod) {
                if ($routeToMethod->match($request)) {
                    /**
                     * @var Invoker $invoker
                     */
                    $invoker = $application->getContainer()->get(Invoker::class);

                    return $invoker->invokeMethod(
                        $application->getContainer()->get($this->serviceId),
                        sprintf('%sAction', $routeToMethod->getMethod()),
                        $request->getParameters()
                    );
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
