<?php

namespace Blocks\Http\Flow;

use Blocks\Configuration;
use Blocks\DI\DIContainer;
use Blocks\Http\HttpApplication;
use Blocks\Http\Request;
use Blocks\Http\Session;

class RouteToMethod extends BaseRoute
{

    /**
     * @var string
     */
    private $method;

    /**
     * @var RouteToControllerAsService
     */
    private $routeToController;

    /**
     * RouteTo constructor.
     * @param null|string $pattern
     * @param $method
     * @param null $name
     */
    public function __construct($pattern, $method, $name = null)
    {
        parent::__construct($pattern, $name);
        $this->method = $method;
        $this->routeToController = null;
    }

    /**
     * @param RouteToControllerAsService $routeToController
     * @return RouteToMethod
     */
    public function setRouteToController($routeToController)
    {
        $this->routeToController = $routeToController;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Request $request)
    {
        if ($this->match($request)) {
            $controller = $this->routeToController->getController();
            $methodName = sprintf('%sAction', $this->method);

            try {
                $reflectionMethod = new \ReflectionMethod($controller, $methodName);
            } catch (\ReflectionException $e) {
                throw new \Exception(sprintf(
                        'Not found method [%s] in controller [%s]',
                        $methodName,
                        get_class($controller))
                );
            }

            $requestParameters = $request->getParameters();
            $parameters = [];
            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $paramClass = $reflectionParameter->getClass();
                $paramName = $reflectionParameter->getName();
                if (isset($paramClass)) {
                    //TODO converters
                    if ($paramClass->getName() === Request::class) {
                        $parameters[$paramName] =
                            HttpApplication::getInstance()->getContainer()->get(HttpApplication::REQUEST);
                    } elseif ($paramClass->getName() === Session::class) {
                        $parameters[$paramName] =
                            HttpApplication::getInstance()->getContainer()->get(HttpApplication::SESSION);
                    } elseif ($paramClass->getName() === Configuration::class) {
                        $parameters[$paramName] =
                            HttpApplication::getInstance()->getConfiguration();
                    } elseif ($paramClass->getName() === DIContainer::class) {
                        $parameters[$paramName] =
                            HttpApplication::getInstance()->getContainer();
                    }
                } elseif (isset($requestParameters[$paramName])) {
                    $parameters[$paramName] = $requestParameters[$paramName];
                } elseif ($reflectionParameter->isDefaultValueAvailable()) {
                    $parameters[$paramName] = $reflectionParameter->getDefaultValue();
                } else {
                    throw new \Exception(sprintf(
                            'Can not resolve parameter "%s" in action "%s" in controller "%s"',
                            $paramName,
                            $methodName,
                            get_class($controller))
                    );
                }
            }
            return $reflectionMethod->invokeArgs($controller, $parameters);
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function findByName($name)
    {
        if ($this->getName() === $name) {
            return $this;
        }
        return null;
    }
}
