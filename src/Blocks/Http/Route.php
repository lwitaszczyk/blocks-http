<?php

namespace Blocks\Http;

use Blocks\Http\Routing\Parameter;

interface Route
{

    /**
     * @param Request $request
     * @return bool
     */
    public function match(Request $request);

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

    /**
     * @return Parameter
     */
    public function getParameters();

    /**
     * @param Route $parent
     * @return $this
     */
    public function setParent(Route $parent);

    /**
     * @return Route
     */
    public function getParent();
}
