<?php

namespace Blocks\Http\Response;

use Blocks\Http\Response;

class ResponseAsJSON extends Response
{

    /**
     * @param string $json
     * @param int    $status
     */
    public function __construct($json, $status = self::HTTP_OK)
    {
        parent::__construct($json, $status);
        $this->setContentType(self::CONTENT_TYPE_APPLICATION_JSON);
    }
}
