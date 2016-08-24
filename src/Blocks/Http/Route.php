<?php

namespace Blocks\Http;

use Blocks\Application;

interface Route
{
    /**
     * @param Request $request
     * @param bool $exact
     * @return bool
     * @throws \Exception
     */
    public function match(Request $request, $exact = true);

    /**
     * @param Application $application
     * @param Request $request
     * @return mixed
     */
    public function process(Application $application, Request $request);

    /**
     * @param string $name
     * @return Route|null
     */
    public function findByName($name);

    /**
     * @param array $params
     * @return string
     */
    public function generateUrl(array $params = []);

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
