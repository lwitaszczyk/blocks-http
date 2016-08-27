<?php

namespace Blocks\Http;

interface RouteMatcher
{
    /**
     * @param Route $route
     * @param Request $request
     * @param bool $exact
     * @return bool
     */
    public function matchRequestParameters(Route $route, Request $request, $exact);

    /**
     * @param Route $route
     * @param mixed[] $params
     * @return string
     */
    public function generateUrl(Route $route, array $params = []);
}