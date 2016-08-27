<?php

namespace Blocks\Http;

use Blocks\Application;
use Blocks\Configuration;
use Blocks\DI\DIAsSingleton;
use Blocks\DI\DIAsSingletonByProxy;
use Blocks\DI\DIAsValue;
use Blocks\DI\DIByConfiguration;
use Blocks\DI\DIByService;
use Blocks\DI\DIByValue;
use Blocks\Http\Exception\HttpApplicationCanNotRenderOutputException;
use Blocks\Http\Flow\DefaultRouteMatcher;
use Blocks\Http\Native\Cookie;
use Blocks\Http\Native\Session;
use Blocks\Http\Request\RequestFromGlobals;

class HttpApplication extends Application
{

    const SESSION = Session::class;
    const COOKIE = Cookie::class;
    const REQUEST = Request::class;
    const URL_GENERATOR = UrlGenerator::class;
    const ROUTING = 'routing-root';

    /**
     * HttpApplication constructor.
     * @param Request $request
     * @param Configuration $configuration
     */
    public function __construct(
        Request $request = null,
        Configuration $configuration = null
    ) {
        parent::__construct($configuration);

        ob_start();

        if (is_null($request)) {
            $request = new RequestFromGlobals();
        }

        $this->getContainer()->addServices([
            (new DIAsValue(Request::class, $request)),
            (new DIAsSingletonByProxy(Session::class, Session::class))->addArguments([
                new DIByConfiguration('session.sid', 'SID'),
                new DIByConfiguration('session.expire', 60),
            ]),
            (new DIAsSingleton(Cookie::class, Cookie::class)),
            (new DIAsSingleton(UrlGenerator::class, UrlGenerator::class))->addArguments([
                new DIByValue($this),
                new DIByService(self::ROUTING),
            ]),
            (new DIAsSingleton(RouteMatcher::class, DefaultRouteMatcher::class)),
        ]);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function process()
    {
        $routing = $this->getContainer()->get(self::ROUTING);

        $response = $routing->process(
            $this,
            $this->getContainer()->get(Request::class)
        );

        if ($response instanceof Response) {
            $response->send();
        } else {
            throw new HttpApplicationCanNotRenderOutputException();
        }

        return $this;
    }
}
