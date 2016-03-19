<?php

namespace Blocks\Http\Exception;

use Blocks\Http\Response;

class HttpApplicationResponseIsNullException extends \Exception
{

    /**
     */
    public function __construct()
    {
        parent::__construct('Application can not render output because response is null');
    }
}
