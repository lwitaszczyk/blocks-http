<?php

namespace Blocks\Http\Routing;

trait ParametersTrait
{

    /**
     * @var Parameter[]
     */
    private $parameters = [];

    /**
     * @return Parameter[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param Parameter $parameter
     * @return $this
     */
    public function addParameter(Parameter $parameter)
    {
        $this->parameters[] = $parameter;
        return $this;
    }

    /**
     * @param Parameter[] $parameters
     * @return $this
     */
    public function addParameters(array $parameters = [])
    {
        foreach ($parameters as $parameter) {
            $this->addParameter($parameter);
        }
        return $this;
    }
}
