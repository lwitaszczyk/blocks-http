<?php

namespace Blocks\Http\Exception;

use Blocks\Http\Response;

class HttpApplicationCanNotRenderOutputException extends \Exception
{

    /**
     * @param Response|null $response
     */
    public function __construct(Response $response = null)
    {
        parent::__construct(
            sprintf('Application can not render output for class [%s]', get_class($response))
        );
    }
}
