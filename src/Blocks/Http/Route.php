<?php

namespace Blocks\Http;

interface Route
{
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
     * @param Request $request
     * @return $this
     */
    public function generateUrl(Request $request);
}
