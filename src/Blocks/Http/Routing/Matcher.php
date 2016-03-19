<?php

namespace Blocks\Http\Routing;

use Blocks\AttributesTrait;
use Blocks\Http\Request;
use Blocks\Http\Route;
use Blocks\Http\Routing\Exception\MatcherTypeIsUndeterminedException;

class Matcher
{

    const MATCH_EXACT = 1;
    const MATCH_LITERAL_AT_START = 2;

    use AttributesTrait;

    /**
     * @var string|null
     */
    private $pattern;

    /**
     * @var Route
     */
    private $route;

    /**
     * @var int
     */
    private $type;

    /**
     * @var array
     */
    private $parametersValues;

    /**
     * @param string $pattern
     */
    public function __construct($pattern = null, $type = self::MATCH_EXACT)
    {
        $this->pattern = $pattern;
        $this->type = $type;
        $this->parametersValues = [];
    }

    public function setRoute(Route $route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param Request $request
     * @return boolean
     */
    public function matchToCurrentPath(Request $request)
    {
        if (is_null($this->pattern)) {
            return true;
        }

        $this->parametersValues = [];
        if (preg_match($this->getRegex(), $request->getCurrentPath(), $this->parametersValues) === 1) {
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     * @return boolean
     */
    public function matchToAttributes(Request $request)
    {
        foreach ($this->attributes as $parameterName => $parameterValue) {
            $requestParameterValue = $request->getAttribute($parameterName);
            if (isset($requestParameterValue) && ($requestParameterValue !== $parameterValue)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     */
    public function getParametersValues()
    {
        $parameters = [];
        foreach ($this->route->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = $this->parametersValues[$parameter->getName()];
        }
        return $parameters;
    }

    /**
     * @return string
     * @throws MatcherTypeIsUndeterminedException
     */
    public function getRegex()
    {
        $regex = '/^' . str_replace('/', '\/', $this->pattern);

        foreach ($this->route->getParameters() as $parameter) {
            $regex .= $parameter->getRegex();
        }

        if ($this->type === self::MATCH_EXACT) {
            $regex .= '$/';
        } elseif (($this->type === self::MATCH_LITERAL_AT_START)) {
            $regex .= '/';
        } else {
            throw new MatcherTypeIsUndeterminedException('Matcher type is undetermined');
        }

        return $regex;
    }
}
