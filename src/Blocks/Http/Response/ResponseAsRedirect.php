<?php

namespace Blocks\Http\Response;

use Blocks\Http\Response;

class ResponseAsRedirect extends Response
{

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @param string $redirectUrl
     * @param int $status
     */
    public function __construct($redirectUrl, $status = self::HTTP_FOUND)
    {
        parent::__construct('', $status);
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function send()
    {
        header('Location: ' . $this->redirectUrl, true, $this->getStatusCode());
        echo $this->getContent();
        ob_end_flush();

        return $this;
    }
}
