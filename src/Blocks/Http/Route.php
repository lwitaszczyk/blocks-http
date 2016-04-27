<?php

namespace Blocks\Http;

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
     * @param Request $request
     * @return bool
     */
    public function process(Request $request);

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
}
