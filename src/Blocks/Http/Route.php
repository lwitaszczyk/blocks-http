<?php

namespace Blocks\Http;

use Blocks\Http\Routing\Parameter;

interface Route
{

    /**
     * @deprecated
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
     * @deprecated
     * @return Parameter[]
     */
    public function getParameters();

    /**
     * @deprecated
     * @param Route $parent
     * @return $this
     */
    public function setParent(Route $parent);

    /**
     * @deprecated
     * @return Route
     */
    public function getParent();
}
