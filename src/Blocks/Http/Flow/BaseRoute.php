<?php

namespace Blocks\Http\Flow;

use Blocks\AttributesTrait;
use Blocks\Http\Request;
use Blocks\Http\Route;

abstract class BaseRoute implements Route
{
    use AttributesTrait;

    const VARIABLE_REGEX = <<<'REGEX'
\{
    \s* ([a-zA-Z][a-zA-Z0-9_-]*) \s*
    (?:
        : \s* ([^{}]*(?:\{(?-1)\}[^{}]*)*)
    )?
\}
REGEX;

    const DEFAULT_DISPATCH_REGEX = '[^/]+';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var Route
     */
    private $parentRoute;

    /**
     * RouteToService constructor.
     * @param string $name
     * @param string $pattern
     */
    public function __construct($pattern = null, $name = null)
    {
        $this->name = $name;
        $this->pattern = $pattern;
    }

    /**
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public function generateUrl(array $params = [])
    {
        $variants = $this->parse(
            $this->getAbsolutePattern()
        );

        foreach ($variants as $routeVariant) {
            $paramsCount = 0;
            foreach ($routeVariant as $segment) {
                if (is_array($segment)) {
                    $paramsCount++;
                }
            }

            if ($paramsCount === count($params)) {
                return $this->generateUrlFromSegments($params, $routeVariant);
            }
        }

        throw new \Exception(sprintf('Can not generate url for action %s', $this->getName()));
    }

    /**
     * @return string
     */
    public function getAbsolutePattern()
    {
        $absolutePattern = '';

        $parentRoute = $this->getParentRoute();
        if ($parentRoute instanceof self) {
            $absolutePattern = $parentRoute->getAbsolutePattern();
        }

        $absolutePattern .= $this->getPattern();
        return $absolutePattern;
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
     * @return Route
     */
    public function getParentRoute()
    {
        return $this->parentRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function setParentRoute(Route $parentRoute)
    {
        $this->parentRoute = $parentRoute;
        return $this;
    }

    /**
     * @param Request $request
     * @param bool $exact
     * @return bool
     * @throws \Exception
     */
    public function match(Request $request, $exact = true)
    {
        $exact = (bool)$exact;
        foreach ($this->attributes as $parameterName => $parameterValue) {
            $requestParameterValue = $request->getAttribute($parameterName);
            if (!is_null($requestParameterValue) && ($requestParameterValue !== $parameterValue)) {
                return false;
            }
        }

        if (is_null($this->pattern)) {
            return true;
        }

        $pattern = rtrim($this->getAbsolutePattern(), '/');
        $requestPath = rtrim($request->getPath(), '/');

        $segments = $this->parse($pattern);
        foreach ($segments as $segment) {
            $regex = $this->buildRegexForRoute($segment);
            $regex = $exact ? "~^$regex$~" : "~^$regex~";

            $rawParameters = [];
            if ((bool)preg_match($regex, $requestPath, $rawParameters)) {
                foreach ($segment as $item) {
                    if (is_array($item)) {
                        $parameterName = $item[0];
                        $request->setParameter($parameterName, $rawParameters[$parameterName]);
                    }
                }
                return true;
            }
        }

        return false;
    }

    /**
     * @param $route
     * @return array
     * @throws \Exception
     */
    private function parse($route)
    {
        $routeWithoutClosingOptionals = rtrim($route, ']');
        $numOptionals = strlen($route) - strlen($routeWithoutClosingOptionals);
        // Split on [ while skipping placeholders
        $segments = preg_split('~' . self::VARIABLE_REGEX . '(*SKIP)(*F) | \[~x', $routeWithoutClosingOptionals);
        if ($numOptionals !== count($segments) - 1) {
            // If there are any ] in the middle of the route, throw a more specific error message
            if (preg_match('~' . self::VARIABLE_REGEX . '(*SKIP)(*F) | \]~x', $routeWithoutClosingOptionals)) {
                throw new \Exception("Optional segments can only occur at the end of a route");
            }
            throw new \Exception("Number of opening '[' and closing ']' does not match");
        }
        $currentRoute = '';
        $routeSegments = [];
        foreach ($segments as $n => $segment) {
            if ($segment === '' && $n !== 0) {
                throw new \Exception("Empty optional part");
            }
            $currentRoute .= $segment;
            $routeSegments[] = $this->parsePlaceholders($currentRoute);
        }
        return $routeSegments;
    }

    /**
     * @param string $route
     * @return array
     */
    private function parsePlaceholders($route)
    {
        if (!preg_match_all(
            '~' . self::VARIABLE_REGEX . '~x', $route, $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        )
        ) {
            return [$route];
        }
        $offset = 0;
        $routeData = [];
        foreach ($matches as $set) {
            if ($set[0][1] > $offset) {
                $routeData[] = substr($route, $offset, $set[0][1] - $offset);
            }
            $routeData[] = [
                $set[1][0],
                isset($set[2]) ? trim($set[2][0]) : self::DEFAULT_DISPATCH_REGEX
            ];
            $offset = $set[0][1] + strlen($set[0][0]);
        }
        if ($offset != strlen($route)) {
            $routeData[] = substr($route, $offset);
        }
        return $routeData;
    }

    /**
     * @param $routeData
     * @return string
     * @throws \Exception
     */
    private function buildRegexForRoute(array $routeData = [])
    {
        $regex = '';
        $variables = [];
        foreach ($routeData as $part) {
            if (is_string($part)) {
                $regex .= preg_quote($part, '~');
                continue;
            }

            list($varName, $regexPart) = $part;

            if (isset($variables[$varName])) {
                throw new \Exception(sprintf(
                    'Cannot use the same placeholder "%s" twice', $varName
                ));
            }

            if ($this->regexHasCapturingGroups($regexPart)) {
                throw new \Exception(sprintf(
                    'Regex "%s" for parameter "%s" contains a capturing group',
                    $regexPart, $varName
                ));
            }

            $variables[$varName] = $varName;
            $regex .= "(?P<$varName>($regexPart))";
        }

        return $regex;
    }

    /**
     * @param $regex
     * @return bool|int
     */
    private function regexHasCapturingGroups($regex)
    {
        if (false === strpos($regex, '(')) {
            // Needs to have at least a ( to contain a capturing group
            return false;
        }

// Semi-accurate detection for capturing groups
        return preg_match(
            '~
                (?:
                    \(\?\(
                  | \[ [^\]\\\\]* (?: \\\\ . [^\]\\\\]* )* \]
                  | \\\\ .
                ) (*SKIP)(*FAIL) |
                \(
                (?!
                    \? (?! <(?![!=]) | P< | \' )
                  | \*
                )
            ~x',
            $regex
        );
    }

    /**
     * @param mixed[] $params
     * @param array $routeVariant
     * @return string
     * @throws \Exception
     */
    private function generateUrlFromSegments(array $params, $routeVariant)
    {
        $url = '';

        foreach ($routeVariant as $routeSegment) {
            if (is_array($routeSegment)) {
                $key = $routeSegment[0];
                if (array_key_exists($key, $params)) {
                    $url .= $params[$key];
                } else {
                    throw new \Exception(sprintf('Parameter %s for action %s is required', $key, $this->getName()));
                }
            } else {
                $url .= $routeSegment;
            }
        }

        return $url;
    }
}
