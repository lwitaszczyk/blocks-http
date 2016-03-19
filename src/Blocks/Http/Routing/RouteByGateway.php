<?php

namespace Blocks\Http\Routing;

use Blocks\Application;
use Blocks\Http\Request;
use Blocks\Http\Route;
use Blocks\Http\Routing\Exception\HttpNotFoundException;

class RouteByGateway implements Route
{

    use MatchersTrait;
    use RoutesTrait;

    /**
     * @var string
     */
    private $gateway;

    /**
     * @param string $gateway
     */
    public function __construct($gateway)
    {
        $this->gateway = $gateway;
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

        foreach ($this->matchers as $matcher) {
            if ($matcher->matchToAttributes($request)) {
                return $url . $matcher->getPattern();
            }
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Request $request)
    {
        $processResponse = null;

        if ((empty($this->matchers) || !is_null($this->match($request)))) {
            $gateway = $this->getGatewayInstance();

            if ($gateway instanceof Gateway) {
                $incomingResponse = $gateway->incoming($request);
                if (isset($incomingResponse)) {
                    return $incomingResponse;
                }
            }

            foreach ($this->getRoutes() as $route) {
                $processResponse = $route->process($request);
                if (isset($processResponse)) {
                    break;
                }
            }

            if ($gateway instanceof Gateway) {
                $outgoingResponse = $gateway->outgoing($processResponse);
                if (isset($outgoingResponse)) {
                    return $outgoingResponse;
                }
            }
        }

        if (!is_null($processResponse)) {
            return $processResponse;
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
    private function getGatewayInstance()
    {
        if (is_string($this->gateway)) {
            return Application::getInstance()->getContainer()->get($this->gateway);
        } elseif (is_object($this->gateway)) {
            return $this->gateway;
        } else {
            return $this->gateway;
        }
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return [];
    }
}
