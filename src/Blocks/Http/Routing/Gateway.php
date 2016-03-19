<?php

namespace Blocks\Http\Routing;

use Blocks\Http\Request;
use Blocks\Http\Response;

interface Gateway
{

    /**
     * @param Request $request
     * @return Response|null $response
     */
    public function incoming(Request $request);

    /**
     * @param mixed $response
     * @return Response $response
     */
    public function outgoing($response = null);
}
