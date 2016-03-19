<?php

namespace Blocks\Http\Routing;

use Blocks\NamedTrait;
use Blocks\Http\Request;
use Blocks\Http\Route;
use Blocks\Http\Routing\Exception\RouteNotFoundMethodInControllerException;

class RouteToMethod implements Route
{

    use NamedTrait;
    use MatchersTrait;
    use ParametersTrait;
    use RoutesTrait;

    /**
     * @var string
     */
    private $method;

    /**
     * @param string $name
     * @param string $method
     * @param string $pattern
     */
    public function __construct($name, $method, $pattern = null)
    {
        $this->setName($name);
        $this->method = $method;

        if (!is_null($pattern)) {
            $this->addMatcher(new Matcher($pattern));
        }
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
        $matcher = $this->match($request);

        if (!is_null($matcher)) {
            $controller = $this->parent->getControllerInstance();
            $method = $this->method;
            if (is_callable([$controller, $method])) {
                //TODO execute by Reflection and bind params to corresponding parameters
                $response = call_user_func_array([$controller, $method], $matcher->getParametersValues());
                if (is_null($response)) {
                    throw new \Exception('Action return empty result!!!');
                }
                return $response;
            } else {
                throw new RouteNotFoundMethodInControllerException(
                    sprintf(
                        'Route [%s] not found method [%s] in controller [%s]',
                        $this->getName(),
                        $method,
                        get_class($controller)
                    )
                );
            }
        }

        return null;
    }
}
