<?php

namespace Blocks\Http;

interface RouteContainer extends Route
{
    /**
     * @param Route $parentRoute
     * @return $this
     */
    public function setParentRoute(Route $parentRoute);

    /**
     * @return Route|null
     */
    public function getParentRoute();
}
