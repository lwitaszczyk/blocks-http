<?php

namespace Blocks\Http\Flow;

use Blocks\Application;
use Blocks\Http\Request;

class RouteToMethod extends BaseRoute
{

    /**
     * @var string
     */
    private $method;

    /**
     * RouteTo constructor.
     * @param null|string $pattern
     * @param $method
     * @param null $name
     */
    public function __construct($pattern, $method, $name = null)
    {
        parent::__construct($pattern, $name);
        $this->method = $method;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Application $application, Request $request)
    {
        throw new \Exception('Method process can not be executed');
    }

    /**
     * {@inheritDoc}
     */
    public function findByName($name)
    {
        if ($this->getName() === $name) {
            return $this;
        }
        return null;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}
