<?php

namespace Blocks\Http\Exception;

class RouteMatcherException extends \Exception
{
    /**
     * HttpApplicationCanNotFoundRouteException constructor.
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
