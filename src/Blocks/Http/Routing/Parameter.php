<?php

namespace Blocks\Http\Routing;

use Blocks\NamedTrait;

class Parameter
{

    use NamedTrait;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @param string $name
     * @param string $pattern
     */
    public function __construct($name, $pattern = null)
    {
        $this->name = $name;

        if (is_null($pattern)) {
            $pattern = '\S+';
        }
        $this->pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param $default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegex()
    {
        $regex = sprintf('\/(?P<%s>(%s))', $this->name, $this->pattern);
        return $regex;
    }
}
