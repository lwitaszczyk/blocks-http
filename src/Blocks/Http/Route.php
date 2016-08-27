<?php

namespace Blocks\Http;

use Blocks\Application;

interface Route
{
    /**
     * @param Application $application
     * @param Request $request
     * @param bool $exact
     * @return bool
     * @throws \Exception
     */
    public function match(Application $application, Request $request, $exact = true);

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
     * @param Application $application
     * @param array $params
     * @return string
     */
    public function generateUrl(Application $application, array $params = []);

    /**
     * @param Route $parentRoute
     * @return $this
     */
    public function setParentRoute(Route $parentRoute);

    /**
     * @return Route|null
     */
    public function getParentRoute();

    /**
     * @return string|null
     */
    public function getPattern();

    /**
     * @return string|null
     */
    public function getAbsolutePattern();

    /**
     * @return string|null
     */
    public function getName();
}
