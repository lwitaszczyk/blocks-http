<?php

namespace Blocks\Http;

use Blocks\Application;
use Blocks\Configuration;
use Blocks\DI\DIAsProxySingleton;
use Blocks\DI\DIAsSingleton;
use Blocks\DI\DIAsValue;
use Blocks\DI\DIByConfiguration;
use Blocks\DI\DIByService;
use Blocks\Http\Exception\HttpApplicationCanNotFoundRouteException;
use Blocks\Http\Exception\HttpApplicationCanNotRenderOutputException;
use Blocks\Http\Exception\HttpApplicationResponseIsNullException;
use Blocks\Http\Native\Cookie;
use Blocks\Http\Native\Session;
use Blocks\Http\Request\RequestFromGlobals;

class HttpApplication extends Application
{

    const SESSION = 'http-session';
    const COOKIE = 'http-cookie';
    const REQUEST = 'http-request';
    const URL_GENERATOR = 'http-url-generator';
    const ROUTING = 'http-routing';

    /**
     * @var Route
     */
    private $routing;

    /**
     * @var Request
     */
    private $request;

    /**
     * HttpApplication constructor.
     * @param Request $request
     * @param Configuration $configuration
     */
    public function __construct(
        Request $request = null,
        Configuration $configuration = null
    )
    {
        parent::__construct($configuration);

        $this->request = (is_null($request)) ? $this->request = new RequestFromGlobals() : $request;

        $this->getContainer()->add([
            (new DIAsValue(self::REQUEST, $this->request)),
            (new DIAsProxySingleton(self::SESSION, Session::class))->addArguments([
                new DIByConfiguration('session.sid', 'SID'),
                new DIByConfiguration('session.expire', 60),
            ]),
            (new DIAsSingleton(self::COOKIE, Cookie::class)),
            (new DIAsSingleton(self::URL_GENERATOR, UrlGenerator::class))->addArguments([
                new DIByService(self::APPLICATION),
            ]),
        ]);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function process()
    {
        $request = $this->getRequest();
        $routing = $this->getRouting();

        try {
            ob_start();

            $response = $routing->process($request);

            if (is_null($response)) {
                throw new HttpApplicationResponseIsNullException();
            }

            if (!$response instanceof Response) {
                throw new HttpApplicationCanNotRenderOutputException($response);
            }

            $response->send();
        } catch (\Exception $exception) {
            $result = $this->onException($exception);
            if ($result instanceof Response) {
                $result->send();
            } else {
                throw $exception;
            }
        }

        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Route
     */
    public function getRouting()
    {
        if (is_null($this->routing)) {
            $this->routing = $this->getContainer()->get(self::ROUTING);
        }
        return $this->routing;
    }

    /**
     * @param $routeName
     * @param string[] $params
     * @return string
     * @throws HttpApplicationCanNotFoundRouteException
     */
    public function urlByRouteName($routeName, array $params = [])
    {
        $route = $this->findRouteByName($routeName);
        if (empty($route)) {
            throw new HttpApplicationCanNotFoundRouteException($routeName);
        }
        return $this->urlByRoute($route, $params);
    }

    /**
     * @param string $name
     * @return Route
     */
    public function findRouteByName($name)
    {
        return $this->getRouting()->findByName($name);
    }

    /**
     * @param Route $route
     * @param string[] $params
     * @return string
     */
    public function urlByRoute(Route $route, array $params = [])
    {
        $separator = '/';
        $url = $route->generateUrl($this->getRequest());
        if (!empty($params)) {
            $url .= $separator . implode($separator, $params);
        }
        return $separator . trim($url, $separator);
    }
}
