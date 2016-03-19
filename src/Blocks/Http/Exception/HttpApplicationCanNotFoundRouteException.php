<?php

namespace Blocks\Http\Exception;

class HttpApplicationCanNotFoundRouteException extends \Exception
{

    /**
     * HttpApplicationCanNotFoundRouteException constructor.
     */
    public function __construct($routeName)
    {
        parent::__construct(
            sprintf('Action [%s] not found, can not generate url', $routeName)
        );
    }
}
